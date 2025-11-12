# Development Guidelines

## Overview
This document outlines the coding standards, architectural patterns, and development practices established in the RomaRocitas Laravel project. All new development must follow these guidelines to maintain consistency.

## Tech Stack & Dependencies

### Backend
- **Laravel**: v12.0 (latest stable)
- **PHP**: ^8.2 minimum
- **Authentication**: Laravel Jetstream v5.3 + Fortify
- **Testing**: Pest PHP v3.8
- **Debugging**: Laravel Debugbar v3.16
- **Localization**: Laravel-lang/common v6.7 (Spanish support)

### Frontend
- **Build Tool**: Vite v7.0.4
- **CSS Framework**: Tailwind CSS v3.4.0
- **JavaScript**: Alpine.js (via Jetstream)
- **Icons**: Font Awesome v6
- **Notifications**: SweetAlert2 v11
- **HTTP Client**: Axios v1.8.2

### Additional Libraries
- **Livewire**: v3.6.4 (for reactive components)
- **Laravel Sanctum**: v4.0 (API authentication)

## Database & Models

### Migration Standards
- Use anonymous class migrations (Laravel 9+ style)
- Table names: plural, snake_case (e.g., `families`, `product_variants`)
- Primary keys: `id()` method (auto-incrementing bigint)
- Foreign keys: `foreignId('table_id')->constrained()` (assumes singular table name)
- Always include `timestamps()` for created_at/updated_at
- Use appropriate column types: `string()`, `text()`, `float()`, `integer()`, `boolean()`
- Add indexes for frequently queried columns

```php
// Example migration
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('sku');
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('image_path');
    $table->float('price');
    $table->foreignId('subcategory_id')->constrained();
    $table->timestamps();
});
```

### Model Standards
- Extend `Illuminate\Database\Eloquent\Model`
- Use `HasFactory` trait for testing
- Define `$fillable` array for mass assignment
- Use descriptive relationship method names
- Follow Laravel naming conventions for relationships:
  - `belongsTo()`: singular (e.g., `subcategory()`)
  - `hasMany()`: plural (e.g., `products()`)
  - `belongsToMany()`: plural with pivot data if needed

```php
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'image_path',
        'price',
        'subcategory_id'
    ];

    // Relationships
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }
}
```

## Controllers

### Structure
- Extend `App\Http\Controllers\Controller`
- Use resource controllers for CRUD operations
- Group admin controllers in `App\Http\Controllers\Admin\` namespace
- Use route model binding for resource methods

### Validation
- Use `$request->validate()` for form validation
- Define validation rules inline in controller methods
- Use Spanish error messages where appropriate

### Response Patterns
- Return views with `compact()` for data passing
- Use `session()->flash('swal', [...])` for SweetAlert notifications
- Redirect with `redirect()->route()` after operations
- Check for related records before deletion

```php
public function store(Request $request)
{
    $request->validate([
        'name' => 'required'
    ]);

    Family::create($request->all());

    session()->flash('swal', [
        'icon' => 'success',
        'title' => '¡Bien hecho!',
        'text' => 'Familia creada correctamente.'
    ]);

    return redirect()->route('admin.families.index');
}
```

## Views & Blade Templates

### Layout System
- Use `<x-admin-layout>` component for admin pages
- Pass breadcrumbs as array props
- Use `@include()` for partials (navigation, sidebar, breadcrumbs)

### Component Usage
- Use Jetstream form components: `<x-input>`, `<x-label>`, `<x-button>`, `<x-validation-errors>`
- Custom components follow `<x-component-name>` naming
- Use Tailwind CSS classes for styling

### Form Patterns
- Always include `@csrf` token
- Use `old()` helper for form repopulation
- Structure forms with proper semantic HTML
- Use Spanish labels and placeholders

```blade
<form action="{{ route('admin.families.store') }}" method="POST">
    @csrf
    <x-validation-errors class="mb-4" />

    <div class="mb-4">
        <x-label class="mb-2">Nombre</x-label>
        <x-input class="w-full" placeholder="Ingrese el nombre" name="name" value="{{ old('name') }}" />
    </div>

    <div class="flex justify-end">
        <x-button>Guardar</x-button>
    </div>
</form>
```

### Table Patterns
- Use responsive table structure with Tailwind classes
- Include dark mode support (`dark:` prefixes)
- Use pagination with `{{ $items->links() }}`
- Show empty states with informative messages

## Routing

### Route Organization
- Admin routes in `routes/admin.php`
- Use resource routes: `Route::resource('families', FamilyController::class)`
- Public routes in `routes/web.php`
- API routes in `routes/api.php` (if needed)

### Route Naming
- Admin routes: `admin.resource.action` (e.g., `admin.families.index`)
- Follow Laravel resource naming conventions

## JavaScript & Frontend

### Alpine.js Usage
- Use for reactive UI elements (mobile menu, modals)
- Follow Jetstream patterns for component data
- Use `@livewireScripts` for Livewire integration

### SweetAlert Integration
- Use for success/error notifications
- Configure via `session('swal')` in controllers
- Listen for Livewire events: `Livewire.on('swal', data => { ... })`

## File Storage

### Image Handling
- Store images in `storage/app/public/products/`
- Use `Storage::delete()` when removing records
- Generate URLs with `asset()` or `Storage::url()`

## Testing

### Framework
- Use Pest PHP for testing
- Write feature tests for critical functionality
- Follow Laravel testing conventions

## Code Style & Best Practices

### General
- Use Spanish for user-facing text (labels, messages, placeholders)
- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Add PHPDoc comments for complex methods

### Security
- Always validate user input
- Use mass assignment protection (`$fillable`)
- Sanitize file uploads
- Implement proper authorization checks

### Performance
- Use eager loading (`with()`) for relationships
- Implement pagination for large datasets
- Optimize database queries
- Use caching where appropriate

## Development Workflow

### Version Control
- Use descriptive commit messages
- Follow GitFlow or similar branching strategy
- Test before committing

### Deployment
- Use Laravel's deployment best practices
- Run migrations on deployment
- Clear caches: `php artisan optimize:clear`

## Maintenance

### Regular Tasks
- Keep dependencies updated
- Monitor for security vulnerabilities
- Review and optimize database queries
- Update documentation as needed

This document should be updated as new patterns emerge or standards evolve.</content>
<parameter name="filePath">c:\Users\migue\source\Colombia\RomaRocitas\development_guidelines.md