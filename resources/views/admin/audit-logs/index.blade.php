@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Audit Logs</h1>

        <!-- Filters -->
        <form method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Action</label>
                    <select name="action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Actions</option>
                        <option value="create_user" {{ request('action') == 'create_user' ? 'selected' : '' }}>Create User</option>
                        <option value="update_user" {{ request('action') == 'update_user' ? 'selected' : '' }}>Update User</option>
                        <option value="delete_user" {{ request('action') == 'delete_user' ? 'selected' : '' }}>Delete User</option>
                        <option value="delete_product" {{ request('action') == 'delete_product' ? 'selected' : '' }}>Delete Product</option>
                        <option value="failed_login" {{ request('action') == 'failed_login' ? 'selected' : '' }}>Failed Login</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">User</label>
                    <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Users</option>
                        @foreach(\App\Models\User::where('role', 'admin')->get() as $admin)
                            <option value="{{ $admin->id }}" {{ request('user_id') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Filter
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Clear
                </a>
            </div>
        </form>

        <!-- Audit Logs Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($auditLogs as $log)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->user->name ?? 'System' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->model ? class_basename($log->model) . ' #' . $log->model_id : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                @if($log->old_values || $log->new_values)
                                    <details class="cursor-pointer">
                                        <summary class="text-blue-600 hover:text-blue-800">View Changes</summary>
                                        @if($log->old_values)
                                            <div class="mt-2">
                                                <strong>Old Values:</strong>
                                                <pre class="text-xs bg-gray-100 p-2 rounded mt-1">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                        @if($log->new_values)
                                            <div class="mt-2">
                                                <strong>New Values:</strong>
                                                <pre class="text-xs bg-gray-100 p-2 rounded mt-1">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                    </details>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $auditLogs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection