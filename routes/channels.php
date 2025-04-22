<?php


use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('laws', function () {
    return true; // Public channel, accessible without authentication
});

Broadcast::channel('laws', fn () => true); // javni kanal
