<?php

use Z3d0X\FilamentFabricator\Models\Page;

test('the application returns the Fabricator home page', function () {
    Page::create([
        'title' => 'Home',
        'slug' => '/',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $response = $this->get('/');

    $response
        ->assertSuccessful()
        ->assertSee('Home')
        ->assertSee('Main navigation')
        ->assertSee('What is on')
        ->assertSee('Current ticket availability and final prices are confirmed during secure booking.');
});
