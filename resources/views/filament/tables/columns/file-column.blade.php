@php use Illuminate\Support\Facades\Storage; @endphp
<div class="my-3" {{ $getExtraAttributeBag() }}>
    <x-filament::link href="{{ Storage::disk('public')->url($getState()) }}" target="_blank"
                      color="warning" icon="heroicon-o-document-arrow-down" type="button">
        View File
    </x-filament::link>
</div>
