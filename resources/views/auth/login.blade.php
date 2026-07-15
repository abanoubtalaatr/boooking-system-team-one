<x-layouts.auth title="Sign in">
    <main class="auth-page">
        <section class="auth-surface" aria-labelledby="sign-in-title">
            <a class="auth-logo" href="#" aria-label="SFG Medical home"><x-brand-logo /></a>
            <form class="auth-card" method="POST" action="{{ route('login.store') }}">
                @csrf
                <h1 class="auth-title" id="sign-in-title">Sign in</h1>
                <p class="auth-lead">Please provide all information required to access your account</p>

                <div class="field">
                    <label class="field-label" for="email">Email</label>
                    <div class="field-control"><input class="field-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Email" autocomplete="email" required autofocus></div>
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-control">
                        <input class="field-input" id="password" name="password" type="password" placeholder="Password" autocomplete="current-password" aria-describedby="password-hint" required>
                        <button class="password-toggle" data-password-toggle type="button" aria-label="Show password">◉</button>
                    </div>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                    <p class="field-hint" id="password-hint">Must be at least eight characters</p>
                </div>
                <label class="remember-field"><input name="remember" type="checkbox" value="1"> تذكرني</label>
                <a class="auth-link auth-forgot" href="#">Forget the password?</a>
                <button class="auth-button" type="submit">Sign in</button>
                <div class="auth-divider">or</div>
                <button class="auth-button auth-button--google" type="button"><strong class="google-letter"
                        aria-hidden="true">G</strong> Sign up with Google</button>
            </form>
        </section>
    </main>
</x-layouts.auth>
