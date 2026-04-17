@extends('layouts.app')

@section('content')
<div class="container profile-page">
    <section class="submit-header glass-card">
        <h1>{{ __('Profile') }}</h1>
        <p>{{ __('Manage your account information, password, and security settings.') }}</p>
    </section>

    <section class="profile-summary glass-card">
        <div>
            <h2>{{ $user->username ?? $user->name ?? 'User' }}</h2>
            <p>{{ '@' . ($user->username ?? 'user') }}</p>
            <div class="profile-reputation-row">
                <span>{{ __('Reputation Score') }}</span>
                <strong>{{ min(100, max(0, ((int) $user->reports()->where('status', 'verified')->count() * 12))) }}</strong>
            </div>
        </div>
        <div
            class="profile-avatar"
            style="background-image: url('{{ $user->profile_photo_url ?: 'https://picsum.photos/seed/' . urlencode($user->username ?? $user->id) . '/220/220' }}');"
        ></div>
    </section>

    <section class="profile-sections">
        <article class="glass-card profile-block">
            @include('profile.partials.update-profile-information-form')
        </article>

        <article class="glass-card profile-block">
            @include('profile.partials.update-password-form')
        </article>

        <article class="glass-card profile-block">
            @include('profile.partials.delete-user-form')
        </article>

        <article class="glass-card profile-block">
            @include('profile.partials.update-language-form')
        </article>
    </section>
</div>
@endsection
