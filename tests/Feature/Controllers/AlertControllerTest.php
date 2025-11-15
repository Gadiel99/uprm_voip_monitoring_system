<?php

use App\Http\Controllers\AlertController;

test('alert controller class exists', function () {
    expect(class_exists(AlertController::class))->toBeTrue();
});
