<div class="grid grid-cols-2 gap-6">

    <div>
        <label>Name</label>

        <input
            name="name"
            class="w-full border rounded-lg p-2"
            value="{{ old('name',$doctor->name ?? '') }}">
    </div>

    <div>
        <label>Email</label>

        <input
            name="email"
            class="w-full border rounded-lg p-2"
            value="{{ old('email',$doctor->email ?? '') }}">
    </div>

    <div>
        <label>Password</label>

        <input
            type="password"
            name="password"
            class="w-full border rounded-lg p-2">

        @isset($doctor)
            <small>Leave empty to keep current password.</small>
        @endisset
    </div>

    <div>

        <label>Gender</label>

        <select
            name="gender"
            class="w-full border rounded-lg p-2">

            <option value="male"
                @selected(old('gender',$doctor->doctorProfile->gender ?? '')=='male')>
                Male
            </option>

            <option value="female"
                @selected(old('gender',$doctor->doctorProfile->gender ?? '')=='female')>
                Female
            </option>

        </select>

    </div>

    <div>

        <label>Price</label>

        <input
            type="number"
            step="0.01"
            name="price"
            class="w-full border rounded-lg p-2"
            value="{{ old('price',$doctor->doctorProfile->price ?? '') }}">

    </div>

    <div>

        <label>Experience</label>

        <input
            type="number"
            name="experience_years"
            class="w-full border rounded-lg p-2"
            value="{{ old('experience_years',$doctor->doctorProfile->experience_years ?? '') }}">

    </div>

    <div>

        <label>Education</label>

        <input
            name="education"
            class="w-full border rounded-lg p-2"
            value="{{ old('education',$doctor->doctorProfile->education ?? '') }}">

    </div>

    <div>

        <label>Language</label>

        <input
            name="language"
            class="w-full border rounded-lg p-2"
            value="{{ old('language',$doctor->doctorProfile->language ?? '') }}">

    </div>

    <div>

        <label>Bio</label>

        <textarea
            name="bio"
            class="w-full border rounded-lg p-2"
            rows="4">{{ old('bio',$doctor->doctorProfile->bio ?? '') }}</textarea>

    </div>

    <div>

        <label>Status</label>

        <select
            name="is_active"
            class="w-full border rounded-lg p-2">

            <option value="1"
                @selected(old('is_active',$doctor->doctorProfile->is_active ?? 1))>
                Active
            </option>

            <option value="0"
                @selected(old('is_active',$doctor->doctorProfile->is_active ?? 1)==0)>
                Inactive
            </option>

        </select>

    </div>

</div>