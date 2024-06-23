
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
    @foreach($records as $item)
        {{ ($this->folderAction($item))(['record' => $item]) }}
    @endforeach
</div>
