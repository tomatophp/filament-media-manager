@livewire('media-picker', [
    'pickerKey' => $pickerKey,
    'isMultiple' => $isMultiple,
    'collectionName' => $collectionName,
    'maxItems' => $maxItems ?? null,
    'minItems' => $minItems ?? null,
    'initialState' => $currentState ?? null,
])
