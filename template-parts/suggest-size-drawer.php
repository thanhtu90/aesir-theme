<div id="suggest-size-drawer" class="fixed top-0 right-0 w-full md:w-1/4 h-screen bg-white p-4 pt-8 z-[9999] hidden">
    <div class="block mb-4 overflow-hidden">
        <svg class="cursor-pointer float-left hidden" id="ssd-back-btn" xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        <svg class="cursor-pointer float-right" id="ssd-close-btn" xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
    </div>
    <div id="ssd-form">
        <div id="step-1" class="">
            <h5 class="mb-4">Basic information</h5>
            <div class="mb-4">
                <label for="ssd-gender" class="block mb-1">Gender</label>
                <select id="ssd-gender" class="w-full border border-gray-300 p-2">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="ssd-height" class="flex items-center justify-between mb-1">
                    Height
                    <div class="flex items-center gap-2">
                        <label><input type="radio" name="height_unit" value="cm" class="ml-2" checked /> CM</label>
                        <label><input type="radio" name="height_unit" value="in" class="ml-2" /> IN</label>
                    </div>
                </label>
                <select id="ssd-height" class="w-full border border-gray-300 p-2">
                    <?php
                    // Default: show CM options
                    for ($i = 140; $i <= 205; $i++) {
                        echo '<option value="' . $i . '">' . $i . ' CM</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="ssd-weight" class="flex items-center justify-between mb-1">
                    Weight
                    <div class="flex items-center gap-2">
                        <label><input type="radio" name="weight_unit" value="kg" class="ml-2" checked /> KG</label>
                        <label><input type="radio" name="weight_unit" value="lbs" class="ml-2" /> LBS</label>
                    </div>
                </label>
                <select id="ssd-weight" class="w-full border border-gray-300 p-2">
                    <?php
                    // Default: show KG options
                    for ($i = 40; $i <= 120; $i++) {
                        echo '<option value="' . $i . '">' . $i . ' KG</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="ssd-height" class="block mb-1">Age (Optional)</label>
                <select id="ssd-age" class="w-full border border-gray-300 p-2">
                    <option value="">Please select</option>
                    <?php
                    for ($i = 12; $i <= 99; $i++) {
                        echo '<option value="' . $i . '">' . $i . '</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="button" id="ssd-step-1-next-btn" class="button w-full py-2">Next</button>
        </div>
        <div id="step-2-male" class="hidden">
            <h5 class="mb-4">Body shape & preferences</h5>
            <div class="mb-4">
                <label for="ssd-stomach" class="block mb-1">Stomach (Optional)</label>
                <img id="stomach-image"
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/stomach-normal.jpg'); ?>"
                    alt="Stomach" class="!h-32 w-auto mx-auto my-4" />
                <div class="flex items-center justify-center gap-1">
                    <label class="mx-2"><input type="radio" name="stomach" value="flat" /> Flat</label>
                    <label class="mx-2"><input type="radio" name="stomach" value="average" /> Average</label>
                    <label class="mx-2"><input type="radio" name="stomach" value="large" /> Large</label>
                </div>
            </div>
            <div class="mb-4">
                <label for="ssd-chest" class="block mb-1">Chest (Optional)</label>
                <img id="chest-image"
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/chest-normal.jpg'); ?>"
                    alt="Chest" class="!h-32 w-auto mx-auto my-4" />
                <div class="flex items-center justify-center gap-1">
                    <label class="mx-2"><input type="radio" name="chest" value="narrow" /> Narrow</label>
                    <label class="mx-2"><input type="radio" name="chest" value="average" /> Average</label>
                    <label class="mx-2"><input type="radio" name="chest" value="wide" /> Wide</label>
                </div>
            </div>
            <div class="mb-4">
                <!-- Label -->
                <label for="ssd-fit-male" class="block mb-1">Fit Preference</label>
                <p id="ssd-fit-value-male">Normal</p>
                <!-- Range Slider -->
                <div class="relative">
                    <input id="ssd-fit-male" type="range" min="0" max="4" step="1" value="2"
                        class="w-full appearance-none bg-transparent cursor-pointer" />

                    <!-- Custom track -->
                    <div class="absolute top-1/2 left-0 right-0 h-[2px] bg-black -translate-y-1/2 pointer-events-none">
                    </div>

                    <!-- Custom ticks -->
                    <div class="absolute top-1/2 flex justify-between w-full -translate-y-1/2">
                        <?php
                        for ($i = 0; $i <= 4; $i++) {
                            echo '<span class="w-[2px] h-2 bg-black cursor-pointer ssd-fit-tick-male" data-value="' . $i . '"></span>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Labels under slider -->
                <div class="flex justify-between mt-4 text-sm text-gray-700">
                    <span>Very Tight</span>
                    <span>Normal</span>
                    <span>Very Loose</span>
                </div>

                <!-- Tailwind custom styles for range input -->
                <style>
                    input[type="range"]::-webkit-slider-thumb {
                        appearance: none;
                        height: 14px;
                        width: 14px;
                        border-radius: 9999px;
                        background: black;
                        cursor: pointer;
                        margin-top: -6px;
                        /* align with track */
                        position: relative;
                        z-index: 10;
                    }

                    input[type="range"]::-moz-range-thumb {
                        height: 14px;
                        width: 14px;
                        border-radius: 9999px;
                        background: black;
                        cursor: pointer;
                        border: none;
                        position: relative;
                        z-index: 10;
                    }
                </style>
            </div>
            <button type="button" class="button w-full  py-2 ssd-finish-btn">Finish</button>
        </div>
        <div id="step-2-female" class="hidden">
            <h5 class="mb-4">Body shape & preferences</h5>
            <div class="flex items-center justify-between">
                <p>Your measurements</p>
                <div class="flex items-center gap-2">
                    <label><input type="radio" name="measuring_unit" value="cm" class="ml-2" checked /> CM</label>
                    <label><input type="radio" name="measuring_unit" value="in" class="ml-2" /> IN</label>
                </div>
            </div>
            <div class="mb-4">
                <label for="ssd-waist" class="flex items-center justify-between mb-1">
                    Waist (Optional)
                </label>
                <select id="ssd-waist" class="w-full border border-gray-300 p-2">
                    <option value="">Please select</option>
                    <?php
                    // Default: show CM options
                    for ($i = 50; $i <= 140; $i++) {
                        echo '<option value="' . $i . '">' . $i . ' CM</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="ssd-hips" class="flex items-center justify-between mb-1">
                    Hips (Optional)
                </label>
                <select id="ssd-hips" class="w-full border border-gray-300 p-2">
                    <option value="">Please select</option>
                    <?php
                    // Default: show CM options
                    for ($i = 70; $i <= 155; $i++) {
                        echo '<option value="' . $i . '">' . $i . ' CM</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <!-- Label -->
                <label for="ssd-fit-female" class="block mb-1">
                    Fit Preference
                </label>

                <p id="ssd-fit-value-female">Normal</p>

                <!-- Range Slider -->
                <div class="relative">
                    <input id="ssd-fit-female" type="range" min="0" max="4" step="1" value="2"
                        class="w-full appearance-none bg-transparent cursor-pointer" />

                    <!-- Custom track -->
                    <div class="absolute top-1/2 left-0 right-0 h-[2px] bg-black -translate-y-1/2 pointer-events-none">
                    </div>

                    <!-- Custom ticks -->
                    <div class="absolute top-1/2 flex justify-between w-full -translate-y-1/2">
                        <?php
                        for ($i = 0; $i <= 4; $i++) {
                            echo '<span class="w-[2px] h-2 bg-black cursor-pointer ssd-fit-tick-female" data-value="' . $i . '"></span>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Labels under slider -->
                <div class="flex justify-between mt-4 text-sm text-gray-700">
                    <span>Very Tight</span>
                    <span>Normal</span>
                    <span>Very Loose</span>
                </div>

                <!-- Tailwind custom styles for range input -->
                <style>
                    input[type="range"]::-webkit-slider-thumb {
                        appearance: none;
                        height: 14px;
                        width: 14px;
                        border-radius: 9999px;
                        background: black;
                        cursor: pointer;
                        margin-top: -6px;
                        /* align with track */
                        position: relative;
                        z-index: 10;
                    }

                    input[type="range"]::-moz-range-thumb {
                        height: 14px;
                        width: 14px;
                        border-radius: 9999px;
                        background: black;
                        cursor: pointer;
                        border: none;
                        position: relative;
                        z-index: 10;
                    }
                </style>
            </div>
            <button type="button" id="ssd-step-2-next-btn" class="button w-full py-2">Next</button>
        </div>
        <div id="step-3-female" class="hidden">
            <h5 class="mb-4">Bra Size</h5>
            <div class="mb-4">
                <label for="ssd-size-system" class="flex items-center justify-between mb-1">
                    SIZE SYSTEM (Optional)
                </label>
                <select id="ssd-size-system" class="w-full border border-gray-300 p-2">
                    <option value="eu">Size EU</option>
                    <option value="usa">Size USA</option>
                    <option value="uk">Size UK</option>
                    <option value="fr">Size FR</option>
                    <option value="es">Size ES</option>
                    <option value="itl">Size ITL</option>
                    <option value="kr">Size KR</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="ssd-band" class="flex items-center justify-between mb-1">
                    BAND (Optional)
                </label>
                <select id="ssd-band" class="w-full border border-gray-300 p-2">
                    <option value="">Please select</option>
                    <?php
                    for ($i = 60; $i <= 125; $i += 5) {
                        echo '<option value="' . $i . '">' . $i . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="ssd-cup" class="flex items-center justify-between mb-1">
                    Cup (Optional)
                </label>
                <select id="ssd-cup" class="w-full border border-gray-300 p-2">
                    <option value="">Please select</option>
                    <?php
                    $cups = ['AA', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
                    foreach ($cups as $cup) {
                        echo '<option value="' . $cup . '">' . $cup . '</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="button" class="button w-full py-2 ssd-finish-btn">Finish</button>
        </div>
    </div>
    <div id="ssd-result" class="hidden">
        <div class="relative flex items-center justify-center my-2">
            <?php
                global $product;

                if ($product) {
                    echo $product->get_image('woocommerce_thumbnail');
                    // Sizes: woocommerce_thumbnail, woocommerce_single, woocommerce_gallery_thumbnail
                }
            ?>
            <div id="size-error" class="absolute left-0 top-1/2 w-full bg-white bg-opacity-50 text-center"></div>
        </div>
        <p class="text-center my-2">Your size is <span id="ssd-size-value">XS-S</span></p>
        <p class="text-center my-2"><span id="ssd-height-value">159 CM</span> / <span id="ssd-weight-value">59 KG</span>
        </p>

        <button type="button" class="close-drawer w-full button !mt-4">Finish and close</button>
        <button type="button" id="ssd-update-btn" class="w-full button !mt-4">Update</button>
        <button type="button" id="ssd-reset-btn" class="w-full button !mt-4">Reset All</button>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const stomachImages = {
            flat: '<?php echo esc_url(get_template_directory_uri() . "/assets/images/stomach-flat.jpg"); ?>',
            average: '<?php echo esc_url(get_template_directory_uri() . "/assets/images/stomach-average.jpg"); ?>',
            large: '<?php echo esc_url(get_template_directory_uri() . "/assets/images/stomach-large.jpg"); ?>'
        };

        const chestImages = {
            narrow: '<?php echo esc_url(get_template_directory_uri() . "/assets/images/chest-narrow.jpg"); ?>',
            average: '<?php echo esc_url(get_template_directory_uri() . "/assets/images/chest-average.jpg"); ?>',
            wide: '<?php echo esc_url(get_template_directory_uri() . "/assets/images/chest-wide.jpg"); ?>'
        };

        // Check if cookie exists and populate form/result
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
            return null;
        }

        const ssdCookie = getCookie('ssd_data');
        if (ssdCookie) {
            try {
                const data = JSON.parse(ssdCookie);
                const formData = data.formData || {};
                // Fill form fields
                document.getElementById('ssd-gender').value = formData.gender || 'male';
                document.querySelector('input[name="height_unit"][value="' + (formData.height_unit || 'cm') + '"]').checked = true;
                document.querySelector('input[name="weight_unit"][value="' + (formData.weight_unit || 'kg') + '"]').checked = true;
                document.querySelector('input[name="height_unit"]:checked').dispatchEvent(new Event('change'));
                document.querySelector('input[name="weight_unit"]:checked').dispatchEvent(new Event('change'));
                // Set height and weight after options are updated
                setTimeout(function () {
                    document.getElementById('ssd-height').value = formData.height || '';
                    document.getElementById('ssd-weight').value = formData.weight || '';
                }, 0);
                document.getElementById('ssd-age').value = formData.age || '';

                // Male step
                if (formData.stomach) {
                    document.querySelector('input[name="stomach"][value="' + formData.stomach + '"]').checked = true;
                    document.getElementById('stomach-image').src = stomachImages[formData.stomach];
                }
                if (formData.chest) {
                    document.querySelector('input[name="chest"][value="' + formData.chest + '"]').checked = true;
                    document.getElementById('chest-image').src = chestImages[formData.chest];
                }
                document.getElementById('ssd-fit-male').value = formData.fit_male || 2;
                document.getElementById('ssd-fit-value-male').textContent = ['Very Tight', 'Tight', 'Normal', 'Loose', 'Very Loose'][formData.fit_male || 2];

                // Female step
                document.querySelector('input[name="measuring_unit"][value="' + (formData.measuring_unit || 'cm') + '"]').checked = true;
                document.querySelector('input[name="measuring_unit"]:checked').dispatchEvent(new Event('change'));
                document.getElementById('ssd-waist').value = formData.waist || '';
                document.getElementById('ssd-hips').value = formData.hips || '';
                document.getElementById('ssd-fit-female').value = formData.fit_female || 2;
                document.getElementById('ssd-fit-value-female').textContent = ['Very Tight', 'Tight', 'Normal', 'Loose', 'Very Loose'][formData.fit_female || 2];

                // Bra size step
                document.getElementById('ssd-size-system').value = formData.size_system || 'eu';
                document.getElementById('ssd-size-system').dispatchEvent(new Event('change'));
                document.getElementById('ssd-band').value = formData.band || '';
                document.getElementById('ssd-cup').value = formData.cup || '';

                // Show result only
                document.getElementById('ssd-form').classList.add('hidden');
                document.getElementById('step-1').classList.add('hidden');
                document.getElementById('step-2-male').classList.add('hidden');
                document.getElementById('step-2-female').classList.add('hidden');
                document.getElementById('step-3-female').classList.add('hidden');
                document.getElementById('ssd-back-btn').classList.add('hidden');
                document.getElementById('ssd-result').classList.remove('hidden');

                // Fill result
                document.getElementById('ssd-size-value').textContent = data.size || '';
                document.getElementById('ssd-height-value').textContent = (formData.height || '') + ' ' + (formData.height_unit || '').toUpperCase();
                document.getElementById('ssd-weight-value').textContent = (formData.weight || '') + ' ' + (formData.weight_unit || '').toUpperCase();

                // check size availability in product select box to update size-error
                const size = data.size || '';
                const sizeSelect = document.getElementById('size');
                if (sizeSelect) {
                    let flag = false;
                    for (let i = 0; i < sizeSelect.options.length; i++) {
                        if (sizeSelect.options[i].value.toLowerCase() === size.toLowerCase() || sizeSelect.options[i].value.toLowerCase().includes(size.toLowerCase())) {
                            flag = true;
                            break;
                        }
                    }
                    if (flag == false) {
                        document.getElementById('size-error').textContent = 'Suggested size ' + size + ' is not available for this product.';
                    } else {
                        document.getElementById('size-error').textContent = '';
                    }
                }

            } catch (e) {
                // If cookie is invalid, ignore
                console.log(e);
            }
        }

        // Close button functionality
        document.getElementById('ssd-close-btn').addEventListener('click', function () {
            document.getElementById('suggest-size-drawer').classList.add('hidden');
        });

        // Height options
        const cmOptions = [];
        for (let i = 140; i <= 205; i++) {
            cmOptions.push(`<option value="${i}">${i} CM</option>`);
        }

        // Generate FT/IN options from 4 FT 7 IN (55 IN) to 6 FT 9 IN (81 IN)
        const ftInOptions = [];
        for (let inches = 55; inches <= 81; inches++) {
            const ft = Math.floor(inches / 12);
            const in_ = inches % 12;
            ftInOptions.push(`<option value="${inches}">${ft} FT ${in_} IN</option>`);
        }

        const heightSelect = document.getElementById('ssd-height');
        const heightRadios = document.querySelectorAll('input[name="height_unit"]');

        // Weight options
        const kgOptions = [];
        for (let i = 40; i <= 120; i++) {
            kgOptions.push(`<option value="${i}">${i} KG</option>`);
        }
        const lbsOptions = [];
        for (let i = 88; i <= 264; i++) {
            lbsOptions.push(`<option value="${i}">${i} LBS</option>`);
        }

        const weightSelect = document.getElementById('ssd-weight');
        const weightRadios = document.querySelectorAll('input[name="weight_unit"]');

        // Update select boxes when unit radios change
        function updateHeightOptions(unit) {
            heightSelect.innerHTML = (unit === 'cm') ? cmOptions.join('') : ftInOptions.join('');
        }

        function updateWeightOptions(unit) {
            weightSelect.innerHTML = (unit === 'kg') ? kgOptions.join('') : lbsOptions.join('');
        }

        // Keep height/weight units in sync
        function syncUnits(changed) {
            const heightUnit = document.querySelector('input[name="height_unit"]:checked').value;
            const weightUnit = document.querySelector('input[name="weight_unit"]:checked').value;

            if (changed === 'height') {
                if (heightUnit === 'cm' && weightUnit !== 'kg') {
                    document.querySelector('input[name="weight_unit"][value="kg"]').checked = true;
                    updateWeightOptions('kg');
                } else if (heightUnit === 'in' && weightUnit !== 'lbs') {
                    document.querySelector('input[name="weight_unit"][value="lbs"]').checked = true;
                    updateWeightOptions('lbs');
                }
            } else if (changed === 'weight') {
                if (weightUnit === 'kg' && heightUnit !== 'cm') {
                    document.querySelector('input[name="height_unit"][value="cm"]').checked = true;
                    updateHeightOptions('cm');
                } else if (weightUnit === 'lbs' && heightUnit !== 'in') {
                    document.querySelector('input[name="height_unit"][value="in"]').checked = true;
                    updateHeightOptions('in');
                }
            }
        }

        // Listeners
        heightRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                updateHeightOptions(this.value);
                syncUnits('height');
            });
        });

        weightRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                updateWeightOptions(this.value);
                syncUnits('weight');
            });
        });

        // Init defaults
        document.querySelector('input[name="height_unit"]:checked')?.dispatchEvent(new Event('change'));
        document.querySelector('input[name="weight_unit"]:checked')?.dispatchEvent(new Event('change'));

        // Change stomach image by stomach radio
        const stomachRadios = document.querySelectorAll('input[name="stomach"]');
        const stomachImage = document.getElementById('stomach-image');


        stomachRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (stomachImages[this.value]) {
                    stomachImage.src = stomachImages[this.value];
                }
            });
        });

        // Change chest image by chest radio
        const chestRadios = document.querySelectorAll('input[name="chest"]');
        const chestImage = document.getElementById('chest-image');

        chestRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (chestImages[this.value]) {
                    chestImage.src = chestImages[this.value];
                }
            });
        });

        document.querySelectorAll('.ssd-fit-tick').forEach(function (tick) {
            tick.addEventListener('click', function () {
                document.getElementById('ssd-fit').value = this.dataset.value;
                document.getElementById('ssd-fit').dispatchEvent(new Event('input'));
                let fitText = '';
                switch (this.dataset.value) {
                    case '0':
                        fitText = 'Very Tight';
                        break;
                    case '1':
                        fitText = 'Tight';
                        break;
                    case '2':
                        fitText = 'Normal';
                        break;
                    case '3':
                        fitText = 'Loose';
                        break;
                    case '4':
                        fitText = 'Very Loose';
                        break;
                    default:
                        fitText = 'Normal';
                }
                document.getElementById('ssd-fit-value').textContent = fitText;
            });
        });

        document.getElementById('ssd-back-btn').addEventListener('click', function () {
            document.getElementById('step-1').classList.remove('hidden');
            document.getElementById('step-2-male').classList.add('hidden');
            document.getElementById('step-2-female').classList.add('hidden');
            document.getElementById('ssd-back-btn').classList.add('hidden');
        });

        // Step 1 Next button logic
        document.getElementById('ssd-step-1-next-btn').addEventListener('click', function () {
            const gender = document.getElementById('ssd-gender').value;
            document.getElementById('step-1').classList.add('hidden');
            if (gender === 'male') {
                document.getElementById('step-2-male').classList.remove('hidden');
                document.getElementById('step-2-female').classList.add('hidden');
                document.getElementById('ssd-back-btn').classList.remove('hidden');
            } else {
                document.getElementById('step-2-female').classList.remove('hidden');
                document.getElementById('step-2-male').classList.add('hidden');
                document.getElementById('ssd-back-btn').classList.remove('hidden');
            }
        });

        // Step 2 Next button logic
        document.getElementById('ssd-step-2-next-btn').addEventListener('click', function () {
            document.getElementById('step-2-male').classList.add('hidden');
            document.getElementById('step-2-female').classList.add('hidden');
            document.getElementById('step-3-female').classList.remove('hidden');
        });

        // Waist/Hips options for female step
        const waistCmOptions = ['<option value="">Please select</option>'];
        for (let i = 50; i <= 140; i++) {
            waistCmOptions.push(`<option value="${i}">${i} CM</option>`);
        }
        const waistInOptions = ['<option value="">Please select</option>'];
        // 1 FT 8 IN (20 IN) to 4 FT 7 IN (55 IN)
        for (let inches = 20; inches <= 55; inches++) {
            const ft = Math.floor(inches / 12);
            const in_ = inches % 12;
            waistInOptions.push(`<option value="${inches}">${ft} FT ${in_} IN</option>`);
        }

        const hipsCmOptions = ['<option value="">Please select</option>'];
        for (let i = 70; i <= 155; i++) {
            hipsCmOptions.push(`<option value="${i}">${i} CM</option>`);
        }
        const hipsInOptions = ['<option value="">Please select</option>'];
        // 2 FT 4 IN (28 IN) to 5 FT 1 IN (61 IN)
        for (let inches = 28; inches <= 61; inches++) {
            const ft = Math.floor(inches / 12);
            const in_ = inches % 12;
            hipsInOptions.push(`<option value="${inches}">${ft} FT ${in_} IN</option>`);
        }

        const waistSelect = document.getElementById('ssd-waist');
        const hipsSelect = document.getElementById('ssd-hips');
        const measuringRadios = document.querySelectorAll('input[name="measuring_unit"]');

        function updateWaistOptions(unit) {
            waistSelect.innerHTML = (unit === 'cm') ? waistCmOptions.join('') : waistInOptions.join('');
        }
        function updateHipsOptions(unit) {
            hipsSelect.innerHTML = (unit === 'cm') ? hipsCmOptions.join('') : hipsInOptions.join('');
        }

        measuringRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                updateWaistOptions(this.value);
                updateHipsOptions(this.value);
            });
        });

        // Init defaults for waist/hips
        document.querySelector('input[name="measuring_unit"]:checked')?.dispatchEvent(new Event('change'));

        // Fit Preference for Male
        document.querySelectorAll('.ssd-fit-tick-male').forEach(function (tick) {
            tick.addEventListener('click', function () {
                document.getElementById('ssd-fit-male').value = this.dataset.value;
                document.getElementById('ssd-fit-male').dispatchEvent(new Event('input'));
                let fitText = '';
                switch (this.dataset.value) {
                    case '0':
                        fitText = 'Very Tight';
                        break;
                    case '1':
                        fitText = 'Tight';
                        break;
                    case '2':
                        fitText = 'Normal';
                        break;
                    case '3':
                        fitText = 'Loose';
                        break;
                    case '4':
                        fitText = 'Very Loose';
                        break;
                    default:
                        fitText = 'Normal';
                }
                document.getElementById('ssd-fit-value-male').textContent = fitText;
            });
        });
        document.getElementById('ssd-fit-male').addEventListener('input', function () {
            let fitText = '';
            switch (this.value) {
                case '0':
                    fitText = 'Very Tight';
                    break;
                case '1':
                    fitText = 'Tight';
                    break;
                case '2':
                    fitText = 'Normal';
                    break;
                case '3':
                    fitText = 'Loose';
                    break;
                case '4':
                    fitText = 'Very Loose';
                    break;
                default:
                    fitText = 'Normal';
            }
            document.getElementById('ssd-fit-value-male').textContent = fitText;
        });

        // Fit Preference for Female
        document.querySelectorAll('.ssd-fit-tick-female').forEach(function (tick) {
            tick.addEventListener('click', function () {
                document.getElementById('ssd-fit-female').value = this.dataset.value;
                document.getElementById('ssd-fit-female').dispatchEvent(new Event('input'));
                let fitText = '';
                switch (this.dataset.value) {
                    case '0':
                        fitText = 'Very Tight';
                        break;
                    case '1':
                        fitText = 'Tight';
                        break;
                    case '2':
                        fitText = 'Normal';
                        break;
                    case '3':
                        fitText = 'Loose';
                        break;
                    case '4':
                        fitText = 'Very Loose';
                        break;
                    default:
                        fitText = 'Normal';
                }
                document.getElementById('ssd-fit-value-female').textContent = fitText;
            });
        });
        document.getElementById('ssd-fit-female').addEventListener('input', function () {
            let fitText = '';
            switch (this.value) {
                case '0':
                    fitText = 'Very Tight';
                    break;
                case '1':
                    fitText = 'Tight';
                    break;
                case '2':
                    fitText = 'Normal';
                    break;
                case '3':
                    fitText = 'Loose';
                    break;
                case '4':
                    fitText = 'Very Loose';
                    break;
                default:
                    fitText = 'Normal';
            }
            document.getElementById('ssd-fit-value-female').textContent = fitText;
        });

        // Sample arrays for BAND and CUP options by size system
        const bandOptionsBySystem = {
            eu: [
                '<option value="">Please select</option>',
                '<option value="60">60</option>',
                '<option value="65">65</option>',
                '<option value="70">70</option>',
                '<option value="75">75</option>',
                '<option value="80">80</option>',
                '<option value="85">85</option>',
                '<option value="90">90</option>',
                '<option value="95">95</option>',
                '<option value="100">100</option>',
                '<option value="105">105</option>',
                '<option value="110">110</option>',
                '<option value="115">115</option>',
                '<option value="120">120</option>',
                '<option value="125">125</option>'
            ],
            usa: [
                '<option value="">Please select</option>',
                '<option value="28">28</option>',
                '<option value="30">30</option>',
                '<option value="32">32</option>',
                '<option value="34">34</option>',
                '<option value="36">36</option>',
                '<option value="38">38</option>',
                '<option value="40">40</option>',
                '<option value="42">42</option>',
                '<option value="44">44</option>',
                '<option value="46">46</option>',
                '<option value="48">48</option>',
                '<option value="50">50</option>',
                '<option value="52">52</option>',
                '<option value="54">54</option>'
            ],
            uk: [
                '<option value="">Please select</option>',
                '<option value="28">28</option>',
                '<option value="30">30</option>',
                '<option value="32">32</option>',
                '<option value="34">34</option>',
                '<option value="36">36</option>',
                '<option value="38">38</option>',
                '<option value="40">40</option>',
                '<option value="42">42</option>',
                '<option value="44">44</option>',
                '<option value="46">46</option>',
                '<option value="48">48</option>',
                '<option value="50">50</option>',
                '<option value="52">52</option>',
                '<option value="54">54</option>'
            ],
            fr: [
                '<option value="">Please select</option>',
                '<option value="75">75</option>',
                '<option value="80">80</option>',
                '<option value="85">85</option>',
                '<option value="90">90</option>',
                '<option value="95">95</option>',
                '<option value="100">100</option>',
                '<option value="105">105</option>',
                '<option value="110">110</option>',
                '<option value="115">115</option>',
                '<option value="120">120</option>',
                '<option value="125">125</option>',
                '<option value="130">130</option>',
                '<option value="135">135</option>',
                '<option value="140">140</option>'
            ],
            es: [
                '<option value="">Please select</option>',
                '<option value="75">75</option>',
                '<option value="80">80</option>',
                '<option value="85">85</option>',
                '<option value="90">90</option>',
                '<option value="95">95</option>',
                '<option value="100">100</option>',
                '<option value="105">105</option>',
                '<option value="110">110</option>',
                '<option value="115">115</option>',
                '<option value="120">120</option>',
                '<option value="125">125</option>',
                '<option value="130">130</option>',
                '<option value="135">135</option>',
                '<option value="140">140</option>'
            ],
            itl: [
                '<option value="">Please select</option>',
                '<option value="0">0</option>',
                '<option value="1">1</option>',
                '<option value="2">2</option>',
                '<option value="3">3</option>',
                '<option value="4">4</option>',
                '<option value="5">5</option>',
                '<option value="6">6</option>',
                '<option value="7">7</option>',
                '<option value="8">8</option>',
                '<option value="9">9</option>',
                '<option value="10">10</option>',
                '<option value="11">11</option>',
                '<option value="12">12</option>'
            ],
            kr: [
                '<option value="">Please select</option>',
                '<option value="60">60</option>',
                '<option value="65">65</option>',
                '<option value="70">70</option>',
                '<option value="75">75</option>',
                '<option value="80">80</option>',
                '<option value="85">85</option>',
                '<option value="90">90</option>',
                '<option value="95">95</option>',
                '<option value="100">100</option>',
                '<option value="105">105</option>',
                '<option value="110">110</option>',
                '<option value="115">115</option>',
                '<option value="120">120</option>',
                '<option value="125">125</option>'
            ]
        };
        const cupOptionsBySystem = {
            eu: [
                '<option value="">Please select</option>',
                '<option value="AA">AA</option>',
                '<option value="A">A</option>',
                '<option value="B">B</option>',
                '<option value="C">C</option>',
                '<option value="D">D</option>',
                '<option value="E">E</option>',
                '<option value="F">F</option>',
                '<option value="G">G</option>',
                '<option value="H">H</option>',
                '<option value="I">I</option>',
                '<option value="J">J</option>',
                '<option value="K">K</option>'
            ],
            usa: [
                '<option value="">Please select</option>',
                '<option value="AA">AA</option>',
                '<option value="A">A</option>',
                '<option value="B">B</option>',
                '<option value="C">C</option>',
                '<option value="D">D</option>',
                '<option value="E/DD">E/DD</option>',
                '<option value="F/DDD">F/DDD</option>',
                '<option value="G">G</option>',
                '<option value="H">H</option>',
                '<option value="I">I</option>'
            ],
            uk: [
                '<option value="">Please select</option>',
                '<option value="AA">AA</option>',
                '<option value="A">A</option>',
                '<option value="B">B</option>',
                '<option value="C">C</option>',
                '<option value="D">D</option>',
                '<option value="DD">DD</option>',
                '<option value="E">E</option>',
                '<option value="F">F</option>',
                '<option value="FF">FF</option>',
                '<option value="G">G</option>',
                '<option value="GG">GG</option>',
                '<option value="H">H</option>'
            ],
            fr: [
                '<option value="">Please select</option>',
                '<option value="AA">AA</option>',
                '<option value="A">A</option>',
                '<option value="B">B</option>',
                '<option value="C">C</option>',
                '<option value="D">D</option>',
                '<option value="E">E</option>',
                '<option value="F">F</option>',
                '<option value="G">G</option>',
                '<option value="H">H</option>',
                '<option value="I">I</option>',
                '<option value="J">J</option>',
                '<option value="K">K</option>'
            ],
            es: [
                '<option value="">Please select</option>',
                '<option value="AA">AA</option>',
                '<option value="A">A</option>',
                '<option value="B">B</option>',
                '<option value="C">C</option>',
                '<option value="D">D</option>',
                '<option value="E">E</option>',
                '<option value="F">F</option>',
                '<option value="G">G</option>',
                '<option value="H">H</option>',
                '<option value="I">I</option>',
                '<option value="J">J</option>',
                '<option value="K">K</option>'
            ],
            itl: [
                '<option value="">Please select</option>',
                '<option value="AA">AA</option>',
                '<option value="A">A</option>',
                '<option value="B">B</option>',
                '<option value="C">C</option>',
                '<option value="D">D</option>',
                '<option value="E">E</option>',
                '<option value="F">F</option>',
                '<option value="G">G</option>',
                '<option value="H">H</option>',
                '<option value="I">I</option>',
                '<option value="J">J</option>',
                '<option value="K">K</option>'
            ],
            kr: [
                '<option value="">Please select</option>',
                '<option value="A">A</option>',
                '<option value="B">B</option>',
                '<option value="C">C</option>',
                '<option value="D">D</option>',
                '<option value="E">E</option>',
                '<option value="F">F</option>',
                '<option value="G">G</option>',
                '<option value="H">H</option>',
                '<option value="I">I</option>',
                '<option value="J">J</option>',
                '<option value="K">K</option>'
            ]
        };

        // Change BAND and CUP options when size system changes
        const sizeSystemSelect = document.getElementById('ssd-size-system');
        const bandSelect = document.getElementById('ssd-band');
        const cupSelect = document.getElementById('ssd-cup');

        sizeSystemSelect.addEventListener('change', function () {
            const system = this.value;
            bandSelect.innerHTML = bandOptionsBySystem[system]?.join('') || bandOptionsBySystem['eu'].join('');
            cupSelect.innerHTML = cupOptionsBySystem[system]?.join('') || cupOptionsBySystem['eu'].join('');
        });

        // Init defaults for BAND and CUP
        sizeSystemSelect.dispatchEvent(new Event('change'));

        document.getElementById('ssd-update-btn').addEventListener('click', function () {
            document.getElementById('ssd-result').classList.add('hidden');
            document.getElementById('ssd-form').classList.remove('hidden');
            document.getElementById('step-1').classList.remove('hidden');
            document.getElementById('step-2-male').classList.add('hidden');
            document.getElementById('step-2-female').classList.add('hidden');
            document.getElementById('step-3-female').classList.add('hidden');
            document.getElementById('ssd-back-btn').classList.add('hidden');
        });

        document.getElementById('ssd-reset-btn').addEventListener('click', function () {
            // Hide result, show form and step 1
            document.getElementById('ssd-result').classList.add('hidden');
            document.getElementById('ssd-form').classList.remove('hidden');
            document.getElementById('step-1').classList.remove('hidden');
            document.getElementById('step-2-male').classList.add('hidden');
            document.getElementById('step-2-female').classList.add('hidden');
            document.getElementById('step-3-female').classList.add('hidden');
            document.getElementById('ssd-back-btn').classList.add('hidden');

            // Reset all fields
            document.getElementById('ssd-gender').value = 'male';
            document.querySelector('input[name="height_unit"][value="cm"]').checked = true;
            document.querySelector('input[name="weight_unit"][value="kg"]').checked = true;
            document.getElementById('ssd-height').selectedIndex = 0;
            document.getElementById('ssd-weight').selectedIndex = 0;
            document.getElementById('ssd-age').selectedIndex = 0;

            document.querySelectorAll('input[name="stomach"]').forEach(r => r.checked = false);
            document.getElementById('stomach-image').src = '<?php echo esc_url(get_template_directory_uri() . "/assets/images/stomach-normal.jpg"); ?>';
            document.querySelectorAll('input[name="chest"]').forEach(r => r.checked = false);
            document.getElementById('chest-image').src = '<?php echo esc_url(get_template_directory_uri() . "/assets/images/chest-normal.jpg"); ?>';
            document.getElementById('ssd-fit-male').value = 2;
            document.getElementById('ssd-fit-value-male').textContent = 'Normal';

            document.querySelector('input[name="measuring_unit"][value="cm"]').checked = true;
            document.getElementById('ssd-waist').selectedIndex = 0;
            document.getElementById('ssd-hips').selectedIndex = 0;
            document.getElementById('ssd-fit-female').value = 2;
            document.getElementById('ssd-fit-value-female').textContent = 'Normal';

            document.getElementById('ssd-size-system').value = 'eu';
            document.getElementById('ssd-band').selectedIndex = 0;
            document.getElementById('ssd-cup').selectedIndex = 0;

            // Trigger change events to update options
            document.querySelector('input[name="height_unit"]:checked').dispatchEvent(new Event('change'));
            document.querySelector('input[name="weight_unit"]:checked').dispatchEvent(new Event('change'));
            document.querySelector('input[name="measuring_unit"]:checked').dispatchEvent(new Event('change'));
            document.getElementById('ssd-size-system').dispatchEvent(new Event('change'));

            document.cookie = 'ssd_data=;path=/;expires=Thu, 01 Jan 1970 00:00:00 GMT';

            const sizeSelect = document.getElementById('size');
            if (sizeSelect) {
                for (let i = 0; i < sizeSelect.options.length; i++) {
                    sizeSelect.options[i].text = sizeSelect.options[i].text.replace(' (suggested)', '');
                }
            }
        });

        // Finish button logic
        document.querySelectorAll('.ssd-finish-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                // Here you would typically gather all the input values and calculate the size
                const heightSelect = document.getElementById('ssd-height');
                const weightSelect = document.getElementById('ssd-weight');
                const formData = {
                    gender: document.getElementById('ssd-gender').value,
                    height: heightSelect.value,
                    height_unit: document.querySelector('input[name="height_unit"]:checked')?.value,
                    weight: weightSelect.value,
                    weight_unit: document.querySelector('input[name="weight_unit"]:checked')?.value,
                    age: document.getElementById('ssd-age').value,
                    // Male step
                    stomach: document.querySelector('input[name="stomach"]:checked')?.value,
                    chest: document.querySelector('input[name="chest"]:checked')?.value,
                    fit_male: document.getElementById('ssd-fit-male').value,
                    // Female step
                    measuring_unit: document.querySelector('input[name="measuring_unit"]:checked')?.value,
                    waist: document.getElementById('ssd-waist').value,
                    hips: document.getElementById('ssd-hips').value,
                    fit_female: document.getElementById('ssd-fit-female').value,
                    // Bra size step
                    size_system: document.getElementById('ssd-size-system').value,
                    band: document.getElementById('ssd-band').value,
                    cup: document.getElementById('ssd-cup').value
                };

                // Simple logic to calculate size based on formData
                let size = 'XS-S';

                // Height and weight thresholds (example logic)
                let height = parseInt(formData.height, 10);
                let weight = parseInt(formData.weight, 10);

                // Convert to cm/kg if needed
                if (formData.height_unit === 'in') {
                    height = Math.round(height * 2.54);
                }
                if (formData.weight_unit === 'lbs') {
                    weight = Math.round(weight * 0.453592);
                }

                // Gender-based logic
                if (formData.gender === 'male') {
                    if (height < 165 || weight < 60) {
                        size = 'XS-S';
                    } else if (height < 175 || weight < 75) {
                        size = 'M';
                    } else if (height < 185 || weight < 90) {
                        size = 'L';
                    } else {
                        size = 'XL-XXL';
                    }
                    // Adjust for body shape
                    if (formData.stomach === 'large' || formData.chest === 'wide') {
                        size = (size === 'XS-S') ? 'M' : (size === 'M' ? 'L' : 'XL-XXL');
                    }
                    // Fit preference
                    if (formData.fit_male === '0') size = 'XS-S';
                    if (formData.fit_male === '4') size = 'XL-XXL';
                } else {
                    // Female logic
                    if (height < 155 || weight < 50) {
                        size = 'XS-S';
                    } else if (height < 165 || weight < 65) {
                        size = 'M';
                    } else if (height < 175 || weight < 80) {
                        size = 'L';
                    } else {
                        size = 'XL-XXL';
                    }
                    // Adjust for waist/hips
                    let waist = parseInt(formData.waist, 10);
                    let hips = parseInt(formData.hips, 10);
                    if (formData.measuring_unit === 'in') {
                        waist = Math.round(waist * 2.54);
                        hips = Math.round(hips * 2.54);
                    }
                    if (waist && waist > 90) size = 'L';
                    if (hips && hips > 110) size = 'L';
                    // Fit preference
                    if (formData.fit_female === '0') size = 'XS-S';
                    if (formData.fit_female === '4') size = 'XL-XXL';
                    // Bra size adjustment
                    if (formData.band && formData.cup) {
                        if (formData.cup === 'D' || formData.cup === 'E' || formData.cup === 'F' || formData.cup === 'G') {
                            size = (size === 'XS-S') ? 'M' : (size === 'M' ? 'L' : 'XL-XXL');
                        }
                    }
                }

                // Age adjustment (optional)
                let age = parseInt(formData.age, 10);
                if (age && age < 16) size = 'XS-S';

                // For demonstration, we'll just show a sample size
                document.getElementById('ssd-form').classList.add('hidden');
                document.getElementById('ssd-back-btn').classList.add('hidden');
                document.getElementById('ssd-result').classList.remove('hidden');
                document.getElementById('ssd-size-value').textContent = size;
                document.getElementById('ssd-height-value').textContent = heightSelect.value + ' ' + document.querySelector('input[name="height_unit"]:checked').value.toUpperCase();
                document.getElementById('ssd-weight-value').textContent = weightSelect.value + ' ' + document.querySelector('input[name="weight_unit"]:checked').value.toUpperCase();

                const sizeSelect = document.getElementById('size');
                let flag = false;
                if (sizeSelect) {
                    for (let i = 0; i < sizeSelect.options.length; i++) {
                        // Remove '(suggested)' from all options first
                        for (let j = 0; j < sizeSelect.options.length; j++) {
                            sizeSelect.options[j].text = sizeSelect.options[j].text.replace(' (suggested)', '');
                        }
                        // Then add '(suggested)' to the matched option
                        if (sizeSelect.options[i].value.toLowerCase() === size.toLowerCase() || sizeSelect.options[i].value.toLowerCase().includes(size.toLowerCase())) {
                            sizeSelect.selectedIndex = i;
                            sizeSelect.options[i].text += ' (suggested)';
                            flag = true;
                            break;
                        }
                    }
                }

                if (flag==false) {
                    document.getElementById('size-error').textContent = 'Suggested size ' + size + ' is not available for this product.';
                } else {
                    document.getElementById('size-error').textContent = '';
                }

                // Store formData and size result to cookie (expires in 30 days)
                const cookieData = {
                    formData: formData,
                    size: size
                };
                document.cookie = 'ssd_data=' + encodeURIComponent(JSON.stringify(cookieData)) + ';path=/;max-age=' + (30 * 24 * 60 * 60);

                
            });
        });
    });
</script>