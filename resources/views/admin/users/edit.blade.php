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
        'name' => 'Editar',
    ]

]">

    @livewire('admin.users.user-edit', ['user' => $user])

</x-admin-layout>