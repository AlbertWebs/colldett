@php
    $c = $client ?? [];
@endphp

<article class="admin-card p-6">
    <h3 class="admin-card-title mb-4 text-base">Primary contact</h3>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="admin-label" for="name">Contact name</label>
            <input id="name" name="name" value="{{ old('name', $c['name'] ?? '') }}" class="admin-input @error('name') border-rose-300 @enderror" required autocomplete="name" />
            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="admin-label" for="contact_title">Role / title</label>
            <input id="contact_title" name="contact_title" value="{{ old('contact_title', $c['contact_title'] ?? '') }}" class="admin-input @error('contact_title') border-rose-300 @enderror" placeholder="e.g. Finance Manager" />
            @error('contact_title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="admin-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $c['email'] ?? '') }}" class="admin-input @error('email') border-rose-300 @enderror" required autocomplete="email" />
            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="admin-label" for="phone">Primary phone</label>
            <input id="phone" name="phone" value="{{ old('phone', $c['phone'] ?? '') }}" class="admin-input @error('phone') border-rose-300 @enderror" placeholder="+254 …" autocomplete="tel" />
            @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="admin-label" for="phone_alt">Alternate phone</label>
            <input id="phone_alt" name="phone_alt" value="{{ old('phone_alt', $c['phone_alt'] ?? '') }}" class="admin-input @error('phone_alt') border-rose-300 @enderror" placeholder="Optional" />
            @error('phone_alt') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</article>

<article class="admin-card p-6">
    <h3 class="admin-card-title mb-4 text-base">Company</h3>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="admin-label" for="company">Legal / trading name</label>
            <input id="company" name="company" value="{{ old('company', $c['company'] ?? '') }}" class="admin-input @error('company') border-rose-300 @enderror" required />
            @error('company') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="admin-label" for="industry">Industry / sector</label>
            <input id="industry" name="industry" value="{{ old('industry', $c['industry'] ?? '') }}" class="admin-input @error('industry') border-rose-300 @enderror" placeholder="e.g. Manufacturing" />
            @error('industry') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="admin-label" for="website">Website</label>
            <input id="website" name="website" value="{{ old('website', $c['website'] ?? '') }}" class="admin-input @error('website') border-rose-300 @enderror" placeholder="https://…" inputmode="url" />
            @error('website') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</article>

<article class="admin-card p-6">
    <h3 class="admin-card-title mb-4 text-base">Location</h3>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="admin-label" for="address">Street / building / P.O. Box</label>
            <textarea id="address" name="address" rows="3" class="admin-input min-h-[5rem] @error('address') border-rose-300 @enderror" placeholder="Postal or physical address">{{ old('address', $c['address'] ?? '') }}</textarea>
            @error('address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="admin-label" for="city">City / town</label>
            <input id="city" name="city" value="{{ old('city', $c['city'] ?? '') }}" class="admin-input @error('city') border-rose-300 @enderror" />
            @error('city') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="admin-label" for="country">Country</label>
            <input id="country" name="country" value="{{ old('country', $c['country'] ?? '') }}" class="admin-input @error('country') border-rose-300 @enderror" placeholder="Kenya" />
            @error('country') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</article>

<article class="admin-card p-6">
    <h3 class="admin-card-title mb-4 text-base">Billing &amp; compliance</h3>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="admin-label" for="tax_pin">KRA PIN / tax ID</label>
            <input id="tax_pin" name="tax_pin" value="{{ old('tax_pin', $c['tax_pin'] ?? '') }}" class="admin-input @error('tax_pin') border-rose-300 @enderror" placeholder="If applicable" />
            @error('tax_pin') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</article>

<article class="admin-card p-6">
    <h3 class="admin-card-title mb-4 text-base">Status &amp; notes</h3>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="admin-label" for="status">Account status</label>
            <select id="status" name="status" class="admin-select @error('status') border-rose-300 @enderror">
                <option value="active" @selected(old('status', $c['status'] ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $c['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
            @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="admin-label" for="notes">Internal notes</label>
            <textarea id="notes" name="notes" rows="4" class="admin-input min-h-[6rem] @error('notes') border-rose-300 @enderror" placeholder="Engagement context, billing terms, cautions — internal use only">{{ old('notes', $c['notes'] ?? '') }}</textarea>
            @error('notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</article>
