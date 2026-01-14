(() => {
    const slugify = (value) =>
        (value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

    const initMultiDropdowns = (form) => {
        form.querySelectorAll('[data-multi-dropdown]').forEach((root) => {
            if (root.dataset.jsInit === '1') return;
            root.dataset.jsInit = '1';

            const trigger = root.querySelector('[data-dropdown-trigger]');
            const panel = root.querySelector('[data-dropdown-panel]');
            const placeholder = root.querySelector('[data-dropdown-placeholder]');
            const chipsWrap = root.querySelector('[data-dropdown-chips]');
            const hiddenWrap = root.querySelector('[data-hidden-inputs]');
            const checkboxes = Array.from(root.querySelectorAll('[data-color-checkbox]'));
            const clearBtn = root.querySelector('[data-clear]');
            const doneBtn = root.querySelector('[data-done]');

            if (!trigger || !panel || !hiddenWrap) return;

            const close = () => panel.classList.add('hidden');
            const toggle = () => panel.classList.toggle('hidden');

            const syncHiddenInputs = (ids) => {
                hiddenWrap.innerHTML = '';
                ids.forEach((id) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'color_ids[]';
                    input.value = id;
                    hiddenWrap.appendChild(input);
                });
            };

            const render = () => {
                const selected = checkboxes
                    .filter((cb) => cb.checked)
                    .map((cb) => ({ id: cb.value, name: cb.dataset.colorName || cb.value }));

                if (selected.length === 0) {
                    placeholder?.classList.remove('hidden');
                    chipsWrap?.classList.add('hidden');
                    if (chipsWrap) chipsWrap.innerHTML = '';
                } else {
                    placeholder?.classList.add('hidden');
                    chipsWrap?.classList.remove('hidden');
                    if (chipsWrap) {
                        chipsWrap.innerHTML = selected
                            .map(
                                (s) =>
                                    `<span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">${s.name}</span>`
                            )
                            .join('');
                    }
                }

                syncHiddenInputs(selected.map((s) => s.id));
            };

            render();

            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                toggle();
            });

            doneBtn?.addEventListener('click', close);
            clearBtn?.addEventListener('click', () => {
                checkboxes.forEach((cb) => (cb.checked = false));
                render();
            });
            checkboxes.forEach((cb) => cb.addEventListener('change', render));

            document.addEventListener('click', (e) => {
                if (!root.contains(e.target)) close();
            });
        });
    };

    const initImagePreview = (form) => {
        const input = form.querySelector('[data-image-input]');
        const preview = form.querySelector('[data-image-preview]');
        if (!input || !preview) return;

        const render = () => {
            preview.innerHTML = '';
            const files = Array.from(input.files || []);
            if (files.length === 0) {
                preview.classList.add('hidden');
                return;
            }
            preview.classList.remove('hidden');
            files.forEach((file) => {
                const url = URL.createObjectURL(file);
                const wrapper = document.createElement('div');
                wrapper.className =
                    'overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60';
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Selected product image';
                img.className = 'h-32 w-full object-cover';
                img.onload = () => URL.revokeObjectURL(url);
                wrapper.appendChild(img);
                preview.appendChild(wrapper);
            });
        };

        input.addEventListener('change', render);
        render();
    };

    const initProductForm = (form) => {
        if (!form || form.dataset.jsInit === '1') return;
        form.dataset.jsInit = '1';

        const generateSkuButton = form.querySelector('#generateSkuButton');
        const skuInput = form.querySelector('#skuInput');
        const nameInput = form.querySelector('[data-name-input]');
        const defaultLocaleNameInput = form.querySelector('[data-name-locale-input]');
        const storageKey = 'admin.productForm.locale';

        let slugTouched = false;
        let slugTimer = null;

        const debounce = (callback, delay = 300) => {
            return (...args) => {
                if (slugTimer) {
                    window.clearTimeout(slugTimer);
                }
                slugTimer = window.setTimeout(() => callback(...args), delay);
            };
        };

        const syncDefaultLocaleName = () => {
            if (!nameInput || !defaultLocaleNameInput) return;
            if (defaultLocaleNameInput.value !== nameInput.value) {
                defaultLocaleNameInput.value = nameInput.value;
                defaultLocaleNameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        const resolveActivePanel = () =>
            form.querySelector('[data-locale-panel]:not(.hidden)') ||
            form.querySelector('[data-locale-panel]');

        const getSlugInput = () => {
            const panel = resolveActivePanel();
            return panel ? panel.querySelector('input[id^="slug"]') : null;
        };

        form.querySelectorAll('input[id^="slug"]').forEach((input) => {
            input.addEventListener('input', (event) => {
                if (!event.isTrusted) return;
                slugTouched = input.value.trim().length > 0;
            });
        });

        if (nameInput) {
            const updateSlug = debounce(() => {
                syncDefaultLocaleName();
                const slugInput = getSlugInput();
                if (!slugInput) return;
                if (slugTouched && slugInput.value.trim().length > 0) return;
                slugInput.value = slugify(nameInput.value);
                slugInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
            nameInput.addEventListener('input', updateSlug);
        }

        if (generateSkuButton && skuInput) {
            const generateSku = () => {
                const stamp = Date.now().toString().slice(-4);
                const random = Math.random().toString(36).slice(2, 6).toUpperCase();
                return `PRD-${stamp}${random}`;
            };

            generateSkuButton.addEventListener('click', () => {
                skuInput.value = generateSku();
                skuInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }

        const buttons = Array.from(form.querySelectorAll('[data-product-tab]'));
        const panels = Array.from(form.querySelectorAll('[data-locale-panel]'));

        if (buttons.length && panels.length) {
            const activeFallback = 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-50';
            const inactiveFallback =
                'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50';

            const getClasses = (button, key, fallback) => {
                const raw = button.dataset[key] || fallback;
                return raw.split(' ').filter(Boolean);
            };

            const setActive = (target, persist = true) => {
                if (!target) return;

                buttons.forEach((button) => {
                    const code = button.getAttribute('data-product-tab');
                    const isActive = code === target;
                    const activeClasses = getClasses(button, 'tabActive', activeFallback);
                    const inactiveClasses = getClasses(button, 'tabInactive', inactiveFallback);

                    activeClasses.forEach((cls) => button.classList.toggle(cls, isActive));
                    inactiveClasses.forEach((cls) => button.classList.toggle(cls, !isActive));
                });

                panels.forEach((panel) => {
                    panel.classList.toggle(
                        'hidden',
                        panel.getAttribute('data-locale-panel') !== target
                    );
                });

                if (persist) {
                    try {
                        sessionStorage.setItem(storageKey, target);
                    } catch (error) {
                        // Ignore storage errors.
                    }
                }
            };

            let stored = null;
            try {
                stored = sessionStorage.getItem(storageKey);
            } catch (error) {
                stored = null;
            }

            const visibleTab = buttons
                .map((button) => button.getAttribute('data-product-tab'))
                .find((code) =>
                    form.querySelector(`[data-locale-panel="${code}"]:not(.hidden)`)
                );

            const isStoredValid =
                stored && buttons.some((button) => button.getAttribute('data-product-tab') === stored);
            const defaultTab = isStoredValid
                ? stored
                : visibleTab || buttons[0]?.getAttribute('data-product-tab');

            if (defaultTab) {
                setActive(defaultTab, false);
            }

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    setActive(button.getAttribute('data-product-tab'));
                });
            });
        }

        initMultiDropdowns(form);
        initImagePreview(form);

        form.addEventListener('submit', (e) => {
            const hasAny = form.querySelectorAll('input[name="color_ids[]"]').length > 0;
            if (!hasAny) {
                e.preventDefault();
                alert('Please select at least one color.');
            }
        });
    };

    const initProductForms = () => {
        initProductForm(document.getElementById('product-create-form'));
        initProductForm(document.getElementById('product-edit-form'));
    };

    document.addEventListener('DOMContentLoaded', initProductForms);
    document.addEventListener('livewire:navigated', initProductForms);
})();
