#!/usr/bin/env python3
"""
Cursor-compatible MCP Database Server
Designed to work properly with Cursor IDE
"""

import json
import sys
import os
import psycopg2
from psycopg2.extras import RealDictCursor
import signal
import atexit

class CursorCompatibleMCPServer:
    def __init__(self):
        self.setup_database()
        self.initialized = False
        self.running = True

        # Setup signal handlers
        signal.signal(signal.SIGINT, self.signal_handler)
        signal.signal(signal.SIGTERM, self.signal_handler)
        atexit.register(self.cleanup)

    def setup_database(self):
        """Setup database connection"""
        try:
            self.connection = psycopg2.connect(
                host=os.getenv('DB_HOST', 'localhost'),
                port=os.getenv('DB_PORT', '5433'),
                database=os.getenv('DB_DATABASE', 'cctv_dashboard'),
                user=os.getenv('DB_USERNAME', 'postgres'),
                password=os.getenv('DB_PASSWORD', 'kambin')
            )
            self.cursor = self.connection.cursor(cursor_factory=RealDictCursor)
            # Log to stderr so it doesn't interfere with JSON-RPC
            print("Database connected successfully", file=sys.stderr)
        except Exception as e:
            print(f"Database connection failed: {e}", file=sys.stderr)
            sys.exit(1)

    def signal_handler(self, signum, frame):
        """Handle shutdown signals"""
        print(f"Received signal {signum}, shutting down...", file=sys.stderr)
        self.running = False
        self.cleanup()
        sys.exit(0)

    def cleanup(self):
        """Cleanup resources"""
        try:
            if hasattr(self, 'cursor'):
                self.cursor.close()
            if hasattr(self, 'connection'):
                self.connection.close()
            print("Database connection closed", file=sys.stderr)
        except:
            pass

    def execute_query(self, query, params=None):
        """Execute SQL query"""
        try:
            if params:
                self.cursor.execute(query, params)
            else:
                self.cursor.execute(query)

            if query.strip().upper().startswith('SELECT'):
                results = self.cursor.fetchall()
                # Convert to JSON-serializable format
                data = []
                for row in results:
                    row_dict = {}
                    for key, value in row.items():
                        if hasattr(value, 'isoformat'):  # datetime objects
                            row_dict[key] = value.isoformat()
                        elif hasattr(value, '__float__'):  # Decimal objects
                            row_dict[key] = float(value)
                        else:
                            row_dict[key] = value
                    data.append(row_dict)

                return {
                    'success': True,
                    'data': data,
                    'row_count': len(data)
                }
            else:
                self.connection.commit()
                return {
                    'success': True,
                    'affected_rows': self.cursor.rowcount
                }
        except Exception as e:
            return {'success': False, 'error': str(e)}

    def get_schema(self, table_name=None):
        """Get schema information"""
        try:
            if table_name:
                query = """
                SELECT column_name, data_type, is_nullable
                FROM information_schema.columns
                WHERE table_name = %s
                ORDER BY ordinal_position
                """
                self.cursor.execute(query, (table_name,))
                columns = self.cursor.fetchall()
                return {'success': True, 'columns': [dict(col) for col in columns]}
            else:
                query = """
                SELECT table_name, table_type
                FROM information_schema.tables
                WHERE table_schema = 'public'
                ORDER BY table_name
                """
                self.cursor.execute(query)
                tables = self.cursor.fetchall()
                return {'success': True, 'tables': [dict(table) for table in tables]}
        except Exception as e:
            return {'success': False, 'error': str(e)}

    def get_table_stats(self, table_name):
        """Get table statistics"""
        try:
            count_query = f"SELECT COUNT(*) as row_count FROM {table_name}"
            self.cursor.execute(count_query)
            row_count = self.cursor.fetchone()['row_count']

            return {
                'success': True,
                'table': table_name,
                'row_count': row_count
            }
        except Exception as e:
            return {'success': False, 'error': str(e)}

    def handle_request(self, request):
        """Handle MCP request with proper initialization"""
        method = request.get('method')
        params = request.get('params', {})
        request_id = request.get('id')

        try:
            # Handle initialization
            if method == 'initialize':
                self.initialized = True
                return {
                    'jsonrpc': '2.0',
                    'id': request_id,
                    'result': {
                        'protocolVersion': '2024-11-05',
                        'capabilities': {
                            'tools': {}
                        },
                        'serverInfo': {
                            'name': 'cctv-database-mcp',
                            'version': '1.0.0'
                        }
                    }
                }

            # Handle initialized notification
            elif method == 'notifications/initialized':
                return None  # No response needed for notifications

            # Handle tools/list
            elif method == 'tools/list':
                if not self.initialized:
                    return {
                        'jsonrpc': '2.0',
                        'id': request_id,
                        'error': {
                            'code': -32002,
                            'message': 'Server not initialized'
                        }
                    }

                return {
                    'jsonrpc': '2.0',
                    'id': request_id,
                    'result': {
                        'tools': [
                            {
                                'name': 'execute_query',
                                'description': 'Execute SQL query on the database',
                                'inputSchema': {
                                    'type': 'object',
                                    'properties': {
                                        'query': {
                                            'type': 'string',
                                            'description': 'SQL query to execute'
                                        },
                                        'params': {
                                            'type': 'array',
                                            'description': 'Query parameters (optional)',
                                            'items': {'type': 'string'}
                                        }
                                    },
                                    'required': ['query']
                                }
                            },
                            {
                                'name': 'get_schema',
                                'description': 'Get database schema information',
                                'inputSchema': {
                                    'type': 'object',
                                    'properties': {
                                        'table_name': {
                                            'type': 'string',
                                            'description': 'Specific table name (optional)'
                                        }
                                    }
                                }
                            },
                            {
                                'name': 'get_table_stats',
                                'description': 'Get table statistics (row count, size)',
                                'inputSchema': {
                                    'type': 'object',
                                    'properties': {
                                        'table_name': {
                                            'type': 'string',
                                            'description': 'Table name to get stats for'
                                        }
                                    },
                                    'required': ['table_name']
                                }
                            }
                        ]
                    }
                }

            # Handle tools/call
            elif method == 'tools/call':
                if not self.initialized:
                    return {
                        'jsonrpc': '2.0',
                        'id': request_id,
                        'error': {
                            'code': -32002,
                            'message': 'Server not initialized'
                        }
                    }

                tool_name = params.get('name')
                arguments = params.get('arguments', {})

                if tool_name == 'execute_query':
                    result = self.execute_query(
                        arguments.get('query'),
                        arguments.get('params')
                    )
                elif tool_name == 'get_schema':
                    result = self.get_schema(arguments.get('table_name'))
                elif tool_name == 'get_table_stats':
                    result = self.get_table_stats(arguments.get('table_name'))
                else:
                    return {
                        'jsonrpc': '2.0',
                        'id': request_id,
                        'error': {
                            'code': -32601,
                            'message': f'Unknown tool: {tool_name}'
                        }
                    }

                return {
                    'jsonrpc': '2.0',
                    'id': request_id,
                    'result': {
                        'content': [
                            {
                                'type': 'text',
                                'text': json.dumps(result, indent=2)
                            }
                        ]
                    }
                }

            else:
                return {
                    'jsonrpc': '2.0',
                    'id': request_id,
                    'error': {
                        'code': -32601,
                        'message': f'Unknown method: {method}'
                    }
                }

        except Exception as e:
            return {
                'jsonrpc': '2.0',
                'id': request_id,
                'error': {
                    'code': -32600,
                    'message': f'Internal error: {str(e)}'
                }
            }

    def run(self):
        """Run the server with proper error handling"""
        print("Starting Cursor-compatible MCP server", file=sys.stderr)

        while self.running:
            try:
                line = sys.stdin.readline()
                if not line:
                    break

                line = line.strip()
                if not line:
                    continue

                request = json.loads(line)
                response = self.handle_request(request)

                if response:  # Don't send response for notifications
                    print(json.dumps(response))
                    sys.stdout.flush()

            except json.JSONDecodeError as e:
                print(f"JSON decode error: {e}", file=sys.stderr)
                continue
            except Exception as e:
                print(f"Error processing request: {e}", file=sys.stderr)
                continue

        print("MCP server shutting down", file=sys.stderr)

if __name__ == "__main__":
    server = CursorCompatibleMCPServer()
    server.run()

