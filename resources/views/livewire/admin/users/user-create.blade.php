<div>
    <form wire:submit="store">
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
                    Contraseña
                </x-label>
                <x-input type="password" wire:model="user.password" class="w-full"
                         placeholder="Ingrese la contraseña"/>
            </div>

            <div class="mb-4">
                <x-label class="mb-1">
                    Confirmar Contraseña
                </x-label>
                <x-input type="password" wire:model="user.password_confirmation" class="w-full"
                         placeholder="Confirme la contraseña"/>
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
                    Crear Usuario
                </x-button>
            </div>
        </div>
    </form>
</div>