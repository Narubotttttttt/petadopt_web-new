<section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden" x-data="profilePhotoUploader()">
    <div class="border-b border-gray-100 px-6 py-5 sm:px-8 bg-gray-50/40">
        <h2 class="text-base sm:text-lg font-extrabold text-gray-900">
            Profile Information
        </h2>
        <p class="mt-0.5 text-xs text-gray-500">
            Update your account display name, email address, and profile avatar.
        </p>
    </div>

    <div class="px-6 py-6 sm:px-8">
        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('patch')

            {{-- Avatar Uploader Row --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2.5">Profile Photo</label>
                <div class="flex items-center gap-4">
                    {{-- Preview Container --}}
                    <div class="relative w-16 h-16 rounded-2xl overflow-hidden border border-gray-200 shadow-xs flex-shrink-0 bg-gray-100 flex items-center justify-center">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Avatar preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!photoPreview && currentPhotoUrl">
                            <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!photoPreview && !currentPhotoUrl">
                            <div class="w-full h-full bg-gradient-to-br from-[#EAF5F6] to-[#d3eef1] text-[#199CA4] flex items-center justify-center font-extrabold text-base">
                                {{ $user->initials }}
                            </div>
                        </template>
                    </div>

                    {{-- Upload / Remove Actions --}}
                    <div class="flex items-center gap-2.5">
                        <input type="file" 
                               name="avatar" 
                               id="avatar_input" 
                               accept="image/png,image/jpeg,image/jpg,image/webp" 
                               class="hidden" 
                               @change="handlePhotoChange($event)">

                        <button type="button" 
                                @click="document.getElementById('avatar_input').click()"
                                class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold transition shadow-2xs">
                            Upload Photo
                        </button>

                        <button type="button" 
                                x-show="currentPhotoUrl || photoPreview"
                                @click="removePhoto()"
                                class="px-3.5 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl transition">
                            Remove
                        </button>

                        <input type="hidden" name="remove_avatar" :value="isRemoved ? 1 : 0">
                    </div>
                </div>
                <p class="text-[11px] text-gray-400 mt-2">Recommended square image (JPG, PNG, or WEBP, max 5MB).</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>

            {{-- Name & Email Inputs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Full Name</label>
                    <input id="name" 
                           name="name" 
                           type="text" 
                           value="{{ old('name', $user->name) }}"
                           required 
                           autocomplete="name" 
                           class="w-full px-4 py-2.5 text-xs sm:text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition">
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           value="{{ old('email', $user->email) }}"
                           required 
                           autocomplete="username" 
                           class="w-full px-4 py-2.5 text-xs sm:text-sm bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#199CA4] focus:ring-2 focus:ring-[#199CA4]/20 transition">
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
            </div>

            {{-- Role Display (Read-Only) --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Assigned System Role</label>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 border border-gray-200/80">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">
                        🛡️
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-900 capitalize">{{ $user->role }}</span>
                        <p class="text-[11px] text-gray-500">
                            {{ $user->role === 'admin' ? 'Full system administrator privileges and user management.' : 'Standard staff access to pets and adoption logs.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Submit Row --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button type="submit" class="px-5 py-2.5 bg-[#199CA4] hover:bg-[#13787F] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                    Save Changes
                </button>

                @if (session('status') === 'profile-updated')
                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200">
                        <span>✓</span>
                        <span>Profile updated successfully!</span>
                    </div>
                @endif
            </div>
        </form>
    </div>
</section>

<script>
    function profilePhotoUploader() {
        return {
            currentPhotoUrl: @js($user->avatar_url),
            photoPreview: null,
            isRemoved: false,

            handlePhotoChange(e) {
                const file = e.target.files[0];
                if (file) {
                    this.isRemoved = false;
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.photoPreview = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            removePhoto() {
                this.photoPreview = null;
                this.currentPhotoUrl = null;
                this.isRemoved = true;
                const input = document.getElementById('avatar_input');
                if (input) input.value = '';
            }
        }
    }
</script>