<div class="form-grid">

    <div class="form-group">

        <label>

            اسم التخصص

        </label>

        <input

            type="text"

            name="name"

            value="{{ old('name', $specialization->name ?? '') }}"

            placeholder="مثال: Cardiology">

        @error('name')

            <small class="error">

                {{ $message }}

            </small>

        @enderror

    </div>

    <div class="form-group">

        <label>
            صورة التخصص
        </label>

        <input class="img" type="file" name="image" id="image" accept="image/*">
        @error('image')
            <small class="error">
                {{ $message }}
            </small>
        @enderror

    </div>

</div>

<div class="image-preview">
    <img
        id="preview"
        src="{{ isset($specialization)
            ? Storage::url($specialization->image)
            : asset('images/no-image.jfif') }}"
        alt="preview">
</div>

<div class="form-actions">

    <button
        class="primary-button">

        حفظ

    </button>

</div>

<script>

const imageInput = document.getElementById('image');

const preview = document.getElementById('preview');

imageInput?.addEventListener('change', function(){

    const file = this.files[0];

    if(!file){

        return;

    }

    preview.src = URL.createObjectURL(file);

});

</script>
