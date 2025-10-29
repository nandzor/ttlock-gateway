<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>TTLock Callback Histories</title>
    <style>
      body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
      table { width: 100%; border-collapse: collapse; }
      th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
      th { background: #f3f4f6; }
      h1 { font-size: 18px; margin: 0 0 10px; }
      .meta { margin-bottom: 12px; color: #555; }
    </style>
  </head>
  <body>
    <h1>TTLock Callback Histories</h1>
    <div class="meta">
      Generated at: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Lock</th>
          <th>Event</th>
          <th>Record Type</th>
          <th>Battery</th>
          <th>User</th>
        </tr>
      </thead>
      <tbody>
        @foreach(($histories ?? []) as $h)
          <tr>
            <td>{{ $h->created_at->format('Y-m-d H:i:s') }}</td>
            <td>{{ $h->lock_id }} ({{ $h->lock_mac }})</td>
            <td>{{ $h->event_type_description }} - {{ $h->message }}</td>
            <td>{{ $h->record_type_description }}</td>
            <td>{{ $h->electric_quantity }}% ({{ $h->battery_level_description }})</td>
            <td>{{ $h->username ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </body>
 </html>


