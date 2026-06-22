<?php

it('persists the chosen locale cookie and returns to a same-host referer', function () {
    $this->withHeaders(['referer' => url('/projects')])
        ->get('/locale/en')
        ->assertRedirect(url('/projects'))
        ->assertCookie('locale', 'en');
});

it('ignores a cross-origin referer and falls back home (open-redirect guard)', function () {
    $this->withHeaders(['referer' => 'https://evil.example.com/phish'])
        ->get('/locale/en')
        ->assertRedirect('/');
});

it('falls back home when no referer is present', function () {
    $this->get('/locale/en')
        ->assertRedirect('/')
        ->assertCookie('locale', 'en');
});

it('rejects an unsupported locale at the route level', function () {
    $this->get('/locale/de')->assertNotFound();
});
