<x-admin-layout  :breadcrumbs="[
    [
        'name' => 'Dashboard',
        'route' => route('admin.dashboard'),
    ],
    [
        'name' => 'Usuarios',
        'route' => route('admin.users.index'),
    ],
    [
        'name' => 'Nuevo',
    ]

]">

    @livewire('admin.users.user-create')

</x-admin-layout>