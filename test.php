<?php

// Function to get a pointer representation of a callback function
function get_callback_pointer(callable $callback): string
{
    // Create a C struct that can hold a pointer
    $ffi = FFI::cdef("typedef struct { void* ptr; } PtrHolder;");

    // Create a new instance of the struct
    $holder = $ffi->new("PtrHolder");

    // Store callback information using object properties
    $wrapper = new class($callback, $holder) {
        private $callback;
        private $holder;

        public function __construct(callable $callback, $holder)
        {
            $this->callback = $callback;
            $this->holder = $holder;

            // Use FFI::addr to get the memory address of the holder
            // This doesn't get us the callback address, but gives us a valid pointer
            $this->holder->ptr = FFI::addr($this->holder);
        }

        public function getPointer()
        {
            return $this->holder->ptr;
        }
    };

    // Get the pointer value
    $ptr = $wrapper->getPointer();

    // Convert the pointer to the desired hexadecimal format
    // We need to get the raw address value from the CData object
    $addr_value = FFI::cast("uintptr_t", $ptr);

    // Format as a hexadecimal string with the desired format
    return sprintf("(0x%08X)", $addr_value->cdata);
}

// Example usage
$callback = function ($x) {
    return $x * 2;
};

// Get and display the pointer
$pointer = get_callback_pointer($callback);
echo "Callback pointer: $pointer\n";