<div class="fi-section-content" style="padding: 32px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1rem;">
        @foreach($records as $item)
            @livewire(\TomatoPHP\FilamentMediaManager\Livewire\FolderComponent::class, ['item' => $item], key('folder-' . $item->id))
        @endforeach
    </div>
</div>

