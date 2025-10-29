# CODEBASE.md - TTLock Management System

## 📋 Overview
This document defines the coding standards, patterns, and architectural guidelines for the TTLock Management System. All AI agents must follow these patterns to maintain consistency and prevent deviation from established practices.

## 🏗️ Architecture Patterns

### 1. Service Layer Pattern
**MANDATORY**: All business logic MUST be in Service classes, not Controllers.

#### BaseService Structure
```php
abstract class BaseService
{
    protected $model;
    protected $searchableFields = [];
    protected $orderByColumn = 'created_at';
    protected $orderByDirection = 'desc';
    
    // Core methods that MUST be used
    public function getPaginate(?string $search = null, int $perPage = 10, array $filters = []): LengthAwarePaginator
    public function search(string $search, int $perPage = 10, array $filters = []): LengthAwarePaginator
    public function paginateFromRequest(Request $request, array $filterKeys = []): LengthAwarePaginator
    public function create(array $data): Model
    public function update(Model $model, array $data): bool
    public function delete(Model $model): bool
    public function findById(int $id): ?Model
}
```

#### Service Implementation Rules
- **MUST** extend `BaseService`
- **MUST** inject model in constructor
- **MUST** define `$searchableFields` array
- **MUST** use `paginateFromRequest()` for web controllers
- **MUST** use `listForExport()` for export functionality

### 2. Controller Pattern
**MANDATORY**: Controllers are thin wrappers that delegate to Services.

#### Web Controller Structure
```php
class ExampleController extends Controller
{
    public function __construct(private ExampleService $service) {}
    
    public function index(Request $request)
    {
        $filterKeys = ['field1', 'field2', 'date_from', 'date_to'];
        $histories = $this->service->paginateFromRequest($request, $filterKeys);
        
        return view('example.index', compact('histories'));
    }
    
    public function export(Request $request, string $format)
    {
        $data = $this->service->listForExport($request);
        return $this->exportService->export($format, $exportClass, $viewName, $data, $fileName);
    }
}
```

#### API Controller Structure
```php
class ExampleController extends BaseController
{
    public function __construct(private ExampleService $service) {}
    
    public function index(Request $request): JsonResponse
    {
        $data = $this->service->paginateFromRequest($request, $filterKeys);
        return $this->paginatedResponse($data);
    }
}
```

### 3. Export Pattern
**MANDATORY**: Use `BaseExportService` for all exports.

#### Export Service Usage
```php
// In Controller
public function export(Request $request, string $format)
{
    $data = $this->service->listForExport($request);
    return $this->exportService->export(
        $format,
        'App\Exports\ExampleExport', // Class name as string
        'example.pdf', // View name for PDF
        $data,
        'Example_Export_' . now()->format('Y-m-d_H-i-s')
    );
}
```

#### Export Class Structure
```php
class ExampleExport implements FromView
{
    public function __construct(private array $data) {}
    
    public function view(): View
    {
        return view('example.excel', $this->data);
    }
}
```

## 🎨 Blade Component Standards

### 1. Component Usage Rules
**MANDATORY**: Use existing components, DO NOT create new ones without approval.

#### Core Components
- `x-card` - For all card layouts
- `x-table` - For all tables
- `x-pagination` - For pagination
- `x-input` - For text inputs
- `x-select` - For dropdowns
- `x-button` - For buttons
- `x-badge` - For status indicators
- `x-stat-card` - For dashboard statistics

#### Component Props Standards
```blade
<!-- Stat Card -->
<x-stat-card 
    title="Total Users" 
    :value="$totalUsers" 
    color="blue" 
    :icon="$icon" 
/>

<!-- Select Component -->
<x-select 
    name="event_type" 
    label="Event Type" 
    :options="$eventTypeOptions" 
    :selected="$filters['event_type'] ?? ''" 
    placeholder="Select event type" 
/>

<!-- Table Component -->
<x-table :headers="['ID', 'Name', 'Status', 'Created At']">
    @foreach($items as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td><x-badge :color="$item->status_color">{{ $item->status }}</x-badge></td>
            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
        </tr>
    @endforeach
</x-table>
```

### 2. View Structure Standards
**MANDATORY**: Follow this exact structure for list pages.

```blade
<x-card>
    <!-- Search and Export Section -->
    <div class="mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <x-input name="search" placeholder="Search..." :value="$search" />
            <x-button type="submit">Search</x-button>
        </form>
        
        <!-- Export Dropdown -->
        <x-dropdown>
            <x-slot name="trigger">
                <x-button variant="secondary">Export</x-button>
            </x-slot>
            <x-dropdown-link href="{{ route('example.export', 'excel') }}">Excel</x-dropdown-link>
            <x-dropdown-link href="{{ route('example.export', 'pdf') }}">PDF</x-dropdown-link>
        </x-dropdown>
    </div>

    <!-- Filters Section -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-select name="status" :options="$statusOptions" />
        <x-input name="date_from" type="date" />
        <x-input name="date_to" type="date" />
        <x-button type="submit">Apply Filters</x-button>
    </div>

    <!-- Table Section -->
    <x-table :headers="$headers">
        {{ $slot }}
    </x-table>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $histories->links() }}
    </div>
</x-card>
```

## 🔧 Database Standards

### 1. Migration Standards
**MANDATORY**: Follow this pattern for all migrations.

```php
Schema::create('example_table', function (Blueprint $table) {
    $table->id();
    $table->string('name')->index();
    $table->string('status')->default('active');
    $table->json('metadata')->nullable();
    $table->boolean('processed')->default(false);
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
    
    // Indexes
    $table->index(['status', 'created_at']);
    $table->index('processed');
});
```

### 2. Model Standards
**MANDATORY**: Follow this pattern for all models.

```php
class Example extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'status', 'metadata'];
    
    protected $casts = [
        'metadata' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];
    
    // Accessors
    public function getStatusDescriptionAttribute(): string
    {
        return $this->status_descriptions[$this->status] ?? 'Unknown';
    }
    
    // Scopes
    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }
    
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }
}
```

## 🚀 API Standards

### 1. Response Format
**MANDATORY**: Use `ApiResponseHelper` for all API responses.

```php
// Success Response
return $this->successResponse($data, 'Operation successful');

// Error Response
return $this->errorResponse('Operation failed', $errors);

// Paginated Response
return $this->paginatedResponse($paginator, 'Data retrieved successfully');

// Validation Error
return $this->validationErrorResponse($errors, 'Validation failed');
```

### 2. Route Standards
**MANDATORY**: Follow this naming convention.

```php
// Web Routes
Route::get('/example', [ExampleController::class, 'index'])->name('example.index');
Route::get('/example/export/{format}', [ExampleController::class, 'export'])->name('example.export');

// API Routes
Route::get('/example', [ExampleController::class, 'index'])->name('v1.example.index');
Route::post('/example', [ExampleController::class, 'store'])->name('v1.example.store');
```

## 📊 TTLock Integration Standards

### 1. Callback Processing
**MANDATORY**: Use `TTLockCallbackHistoryService` for all callback operations.

```php
// In TTLockCallbackController
public function callback(Request $request): JsonResponse
{
    try {
        $history = $this->historyService->processCallback($request);
        return $this->successResponse($history, 'Callback processed successfully');
    } catch (Exception $e) {
        return $this->errorResponse('Callback processing failed: ' . $e->getMessage());
    }
}
```

### 2. Event Type Mapping
**MANDATORY**: Use constants from `TTLockCallbackHistoryService`.

```php
// Event Type Constants
const EVENT_TYPE_MAP = [
    'lock_operation' => 'Lock Operation',
    'passcode_operation' => 'Passcode Operation',
    'fingerprint_operation' => 'Fingerprint Operation',
    'battery_low' => 'Battery Low',
    'tamper_alarm' => 'Tamper Alarm',
];

// Vendor Code Constants
const VENDOR_MESSAGE_OVERRIDES = [
    20 => 'Unlocked via fingerprint',
    29 => 'Unexpected unlock detected',
    44 => 'Tamper alert triggered',
];
```

## 🎯 Dashboard Standards

### 1. Statistics Cards
**MANDATORY**: Use `x-stat-card` component.

```blade
<x-stat-card 
    title="Total Callbacks" 
    :value="$totalCallbacks" 
    color="indigo" 
/>
```

### 2. Chart Integration
**MANDATORY**: Use Chart.js with this pattern.

```blade
<canvas id="exampleChart" height="350"></canvas>

@push('scripts')
<script>
    const ctx = document.getElementById('exampleChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: @json($chartData),
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });
</script>
@endpush
```

## 🔒 Security Standards

### 1. Input Validation
**MANDATORY**: Validate all inputs.

```php
$request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'status' => 'required|in:active,inactive',
]);
```

### 2. Authentication
**MANDATORY**: Use middleware for protected routes.

```php
Route::middleware('auth')->group(function () {
    // Protected routes
});
```

## 📝 Documentation Standards

### 1. Code Comments
**MANDATORY**: Document all public methods.

```php
/**
 * Process TTLock callback and save to history
 *
 * @param Request $request
 * @return TTLockCallbackHistory
 * @throws Exception
 */
public function processCallback(Request $request): TTLockCallbackHistory
```

### 2. README Updates
**MANDATORY**: Update README.md when adding new features.

## ⚠️ CRITICAL RULES

### 1. NEVER DO THESE
- ❌ Put business logic in Controllers
- ❌ Create new Blade components without approval
- ❌ Use raw HTML instead of existing components
- ❌ Skip input validation
- ❌ Hardcode values that should be configurable
- ❌ Create duplicate functionality
- ❌ Ignore existing patterns

### 2. ALWAYS DO THESE
- ✅ Use Service layer for business logic
- ✅ Use existing Blade components
- ✅ Follow naming conventions
- ✅ Validate all inputs
- ✅ Use `BaseService` methods
- ✅ Use `ApiResponseHelper` for API responses
- ✅ Follow the established view structure
- ✅ Use constants for mappings
- ✅ Document public methods

## 🔄 Code Review Checklist

Before submitting code, ensure:
- [ ] Business logic is in Service layer
- [ ] Controllers are thin wrappers
- [ ] Using existing Blade components
- [ ] Following naming conventions
- [ ] Input validation implemented
- [ ] Error handling in place
- [ ] Documentation updated
- [ ] No hardcoded values
- [ ] Following established patterns

---

**This document is the single source of truth for coding standards in this project. All AI agents MUST follow these patterns exactly.**
