<div>
    @if($show)
        <script>
            document.addEventListener('livewire:loaded', () => {
                $wire.on('show-toast', (data) => {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    const iconMap = {
                        'success': 'success',
                        'error': 'error',
                        'warning': 'warning',
                        'info': 'info'
                    };

                    Toast.fire({
                        icon: iconMap[data.type] || 'info',
                        title: data.message
                    });
                });

                $wire.on('hide-toast', () => {
                    $wire.set('show', false);
                });
            });
        </script>
    @endif
</div>