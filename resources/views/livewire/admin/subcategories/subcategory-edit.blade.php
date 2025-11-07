<div>
    <form wire:submit="save">

        <div class="card">

            <x-validation-errors class="mb-4" />

            <div class="mb-4">
                <x-label class="mb-2">
                    Familias
                </x-label>

                <x-select class="w-full" wire:model.live="subcategoryEdit.family_id">
                    <option value="" disabled>Seleccione una familia</option>

                    @foreach ($families as $family)
                        <option value="{{ $family->id }}">
                            {{ $family->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="mb-4">
                    <x-label class="mb-2">
                        Categorias
                    </x-label>

                    <x-select name="category_id" class="w-full" wire:model.live="subcategoryEdit.category_id">
                        <option value="" disabled>Seleccione una categoria</option>

                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}" @selected ( old('category_id')  == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </x-select>

                </div> 

                <div class="mb-4">
                    <x-label class="mb-2">Nombre</x-label>
                    <x-input class="w-full" placeholder="Ingrese el nombre de la categoria" wire:model="subcategoryEdit.name"
                    ></x-input>
                </div>
                <div class="flex justify-end">
                    <x-button>
                        Actualizar
                    </x-button>

                    <x-danger-button class="ml-2" onclick="confirmDelete();">
                        Eliminar
                    </x-danger-button>
                </div>
        </div>
    </form>

    <form action="{{route('admin.subcategories.destroy', $subcategory)}}" method="POST" id="delete-form">
        
        @csrf

        @method('DELETE')

    </form>

     @push('js')
        <script>
            function confirmDelete(){
               
                Swal.fire({
                    title: "Esta seguro?",
                    text: "No podras revertir esto!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Si, borralo!",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        /*Swal.fire({
                            title: "Eliminado!",
                            text: "Su archivo fue eliminado.",
                            icon: "success"
                        });*/
                        document.getElementById('delete-form').submit();
                    }
                });
            }
        </script>
    @endpush

</div>

