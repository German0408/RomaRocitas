<div>
    <form wire:submit="update">
        <x-validation-errors class="mb-4" />

        <div class="card">
            <div class="mb-4">
                <x-label class="mb-1">
                    Nombre
                </x-label>
                <x-input wire:model="user.name" class="w-full"
                         placeholder="Ingrese el nombre del usuario"/>
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Email
                </x-label>
                <x-input type="email" wire:model="user.email" class="w-full"
                         placeholder="Ingrese el email del usuario"/>
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Nueva Contraseña (opcional)
                </x-label>
                <x-input type="password" wire:model="password" class="w-full"
                         placeholder="Deje vacío para mantener la contraseña actual"/>
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Confirmar Nueva Contraseña
                </x-label>
                <x-input type="password" wire:model="password_confirmation" class="w-full"
                         placeholder="Confirme la nueva contraseña"/>
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Rol
                </x-label>
                <x-select class="w-full" wire:model="user.role">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </x-select>
            </div>

            <div class="flex justify-end">
                <x-button>
                    Actualizar Usuario
                </x-button>
            </div>
        </div>
    </form>
</div>