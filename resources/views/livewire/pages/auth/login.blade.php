<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <main class="register-main">

        <div class="register-wrapper login-wrapper">

            <div class="register-card">

                <div class="form-header">

                    <h2>تسجيل الدخول</h2>
                    <p>مرحباً بك في بوابة الشبكة الوطنية الموحدة</p>
                </div>

                <x-auth-session-status
                    class="mb-4"
                    :status="session('status')" />

                <form wire:submit="login" novalidate>

                    <div class="form-grid login-form-grid">

                        {{-- Email --}}
                        <div class="form-group">

                            <label for="email">
                                البريد الإلكتروني
                                <span class="required">*</span>
                            </label>

                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>

                                <input
                                    type="email"
                                    id="email"
                                    wire:model="form.email"
                                    placeholder="example@domain.com"
                                    autocomplete="username"
                                    required
                                    autofocus>

                            </div>

                            @error('form.email')
                                <span class="error-msg">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>


                        {{-- Password --}}
                        <div class="form-group">

                            <label for="password">
                                كلمة المرور
                                <span class="required">*</span>
                            </label>

                            <div class="input-wrapper">

                                <i class="fas fa-lock"></i>

                                <input
                                    type="password"
                                    id="password"
                                    wire:model="form.password"
                                    autocomplete="current-password"
                                    required>

                                <i class="fas fa-eye toggle-password"></i>

                            </div>

                            <div class="password-meta">

                                <label class="checkbox-container remember-me-label">

                                    <input
                                        type="checkbox"
                                        wire:model="form.remember">

                                    <span class="checkmark"></span>

                                    <span>تذكرني</span>

                                </label>

                                @if(Route::has('password.request'))

                                    <a
                                        href="{{ route('password.request') }}"
                                        wire:navigate
                                        class="forgot-password-link">

                                        نسيت كلمة المرور؟

                                    </a>

                                @endif

                            </div>

                            @error('form.password')
                                <span class="error-msg">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="btn-submit"
                        wire:loading.attr="disabled">

                        <span wire:loading.remove>
                            تسجيل الدخول
                        </span>

                        <span wire:loading>
                            جاري تسجيل الدخول...
                        </span>

                    </button>

                </form>

            </div>

        </div>

    </main>

</div>
    


