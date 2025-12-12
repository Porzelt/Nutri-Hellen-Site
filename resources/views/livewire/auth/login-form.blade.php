<div class="min-h-screen flex items-center justify-center bg-brand-light py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border-t-4 border-brand-primary">
        
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-brand-secondary">
                Área Restrita
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Acesso exclusivo para administração
            </p>
        </div>

        <form class="mt-8 space-y-6" wire:submit="login">
            
            <div>
                <label for="email-address" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email" id="email-address" type="email" autocomplete="email" required 
                    class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-brand-primary focus:border-brand-primary sm:text-sm shadow-sm transition" 
                    placeholder="exemplo@email.com">
                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                <input wire:model="password" id="password" type="password" autocomplete="current-password" required 
                    class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-brand-primary focus:border-brand-primary sm:text-sm shadow-sm transition" 
                    placeholder="Sua senha secreta">
                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input wire:model="remember" id="remember-me" type="checkbox" 
                        class="h-4 w-4 text-brand-primary focus:ring-brand-primary border-gray-300 rounded cursor-pointer">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900 cursor-pointer">
                        Manter conectado
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" 
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-brand-primary hover:bg-brand-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary transition transform hover:-translate-y-0.5 shadow-md">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-white/50 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Acessar Painel
                </button>
            </div>
        </form>
    </div>
</div>