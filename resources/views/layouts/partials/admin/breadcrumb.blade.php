@if (count($breadcrumbs))
    
    <nav class="mb-4">

        <ol class="flex flex-wrap text-sm text-gray-500">
        
            @foreach ($breadcrumbs as $item)
                @if (!$loop->first)
                    <li class="px-2 text-slate-700">/</li>
                @endif

                <li class="text-sm leading-normal text-slate-700">
                    @isset($item['route'])
                        <a href="{{$item['route']}}" class="opacity-50">
                            {{ $item['name'] }}
                        </a>
                       
                    @else
                        {{ $item['name'] }}
                        
                    @endisset
                   
                </li>
            @endforeach
        </ol>

        @if (count($breadcrumbs) > 1)
            <h6 class="font-bold">
                {{ end($breadcrumbs)['name'] }}
            </h6>
        @endif

        
    </nav>
@endif