<x-layouts::app :title="__('Punto de venta')">
    <div class="pos-page">
        <div class="pos-shell">
            <header class="pos-header">
                <div>
                    <p class="pos-kicker">Venta rápida</p>
                    <h1>Punto de venta</h1>
                    <p class="pos-context">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                </div>
                <label class="pos-branch-picker">
                    <span>Sucursal</span>
                    <select onchange="window.location.href = '{{ route('punto-venta.index') }}?sucursal=' + this.value">
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ $selectedSucursal?->id === $sucursal->id ? 'selected' : '' }}>{{ $sucursal->nombre_sucursal }}</option>
                        @endforeach
                    </select>
                </label>
            </header>

            <div class="pos-layout">
                <main class="pos-catalog">
                    <div class="pos-search-row">
                        <label class="pos-search">
                            <span aria-hidden="true">⌕</span>
                            <input id="pos-search" type="search" placeholder="Buscar producto o código de barras..." autocomplete="off">
                        </label>
                        <button type="button" class="pos-scan-button" id="pos-scan-button" title="Escanear código de barras">Escanear</button>
                        <button type="button" id="scanner-focus-button" class="hidden" tabindex="-1" aria-hidden="true">Abrir cámara</button>
                        <input id="barcode-input" class="hidden" aria-hidden="true" tabindex="-1">
                    </div>

                    <div class="pos-toolbar">
                        <div class="pos-categories" role="tablist" aria-label="Filtrar productos">
                            <button type="button" class="pos-category is-active" data-category="all">Todos</button>
                            <button type="button" class="pos-category" data-category="available">Disponibles</button>
                            <button type="button" class="pos-category" data-category="low">Stock bajo</button>
                        </div>
                        <span class="pos-product-count" id="pos-product-count">{{ $productos->count() }} productos</span>
                    </div>

                    <div class="pos-product-grid" id="pos-product-grid">
                        @forelse($productos as $producto)
                            @php($productoPos = ['id' => $producto->id, 'name' => $producto->nombre_producto, 'price' => (float) $producto->precio, 'stock' => $producto->stock, 'barcode' => $producto->codigo_barras])
                            <article class="pos-product-card" data-name="{{ strtolower($producto->nombre_producto) }}" data-barcode="{{ $producto->codigo_barras }}" data-stock="{{ $producto->stock }}">
                                <button type="button" class="pos-product-add" data-add-product aria-label="Agregar {{ $producto->nombre_producto }}">
                                    <span class="pos-product-icon" aria-hidden="true">Rx</span>
                                    <span class="pos-product-info">
                                        <strong>{{ $producto->nombre_producto }}</strong>
                                        <small>{{ $producto->codigo_barras }}</small>
                                    </span>
                                    <span class="pos-product-price">${{ number_format($producto->precio, 2) }}</span>
                                    <span class="pos-product-stock">{{ $producto->stock }} disponibles</span>
                                </button>
                                <script type="application/json" data-product-data>{{ json_encode($productoPos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}</script>
                            </article>
                        @empty
                            <div class="pos-empty-catalog">No hay productos disponibles en esta sucursal.</div>
                        @endforelse
                    </div>
                </main>

                <aside class="pos-cart" aria-label="Carrito de venta">
                    <div class="pos-cart-heading">
                        <div>
                            <p class="pos-kicker">Venta actual</p>
                            <h2>Carrito</h2>
                        </div>
                        <span id="pos-cart-count">0 artículos</span>
                    </div>
                    <div class="pos-cart-items" id="pos-cart-items">
                        <div class="pos-cart-empty" id="pos-cart-empty">
                            <span class="pos-cart-empty-icon" aria-hidden="true">⌑</span>
                            <strong>Carrito vacío</strong>
                            <span>Agrega productos desde el panel izquierdo</span>
                        </div>
                    </div>
                    <div class="pos-cart-summary">
                        <div><span>Subtotal</span><strong id="pos-subtotal">$0.00</strong></div>
                        <div><span>Descuento</span><strong>$0.00</strong></div>
                        <div class="pos-total"><span>Total</span><strong id="pos-total">$0.00</strong></div>
                        <button type="button" class="pos-checkout" id="pos-checkout" disabled>Cobrar <span id="pos-checkout-total">$0.00</span></button>
                        <p class="pos-checkout-note">El cobro se habilitará al conectar caja y método de pago.</p>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('pos-search');
            const cards = [...document.querySelectorAll('.pos-product-card')];
            const count = document.getElementById('pos-product-count');
            const cartItems = document.getElementById('pos-cart-items');
            const emptyCart = document.getElementById('pos-cart-empty');
            const cartCount = document.getElementById('pos-cart-count');
            const subtotal = document.getElementById('pos-subtotal');
            const total = document.getElementById('pos-total');
            const checkout = document.getElementById('pos-checkout');
            const checkoutTotal = document.getElementById('pos-checkout-total');
            const scanButton = document.getElementById('pos-scan-button');
            const barcodeInput = document.getElementById('barcode-input');
            const cart = new Map();
            let category = 'all';

            const money = (value) => `$${value.toFixed(2)}`;
            const productFromCard = (card) => JSON.parse(card.querySelector('[data-product-data]').textContent);

            const renderCart = () => {
                cartItems.querySelectorAll('[data-cart-row]').forEach((row) => row.remove());
                let items = 0;
                let amount = 0;

                cart.forEach((item) => {
                    items += item.quantity;
                    amount += item.price * item.quantity;
                    const row = document.createElement('div');
                    row.dataset.cartRow = item.id;
                    row.className = 'pos-cart-row';
                    row.innerHTML = `<div><strong>${item.name}</strong><small>${money(item.price)} c/u</small></div><div class="pos-quantity"><button type="button" data-decrease>-</button><span>${item.quantity}</span><button type="button" data-increase>+</button></div><strong>${money(item.price * item.quantity)}</strong>`;
                    row.querySelector('[data-decrease]').addEventListener('click', () => updateQuantity(item.id, -1));
                    row.querySelector('[data-increase]').addEventListener('click', () => updateQuantity(item.id, 1));
                    cartItems.append(row);
                });

                emptyCart.hidden = items > 0;
                cartCount.textContent = `${items} ${items === 1 ? 'artículo' : 'artículos'}`;
                subtotal.textContent = money(amount);
                total.textContent = money(amount);
                checkoutTotal.textContent = money(amount);
                checkout.disabled = items === 0;
            };

            const updateQuantity = (id, change) => {
                const item = cart.get(id);
                if (! item) return;
                item.quantity = Math.min(item.stock, item.quantity + change);
                if (item.quantity <= 0) cart.delete(id);
                renderCart();
            };

            cards.forEach((card) => card.querySelector('[data-add-product]').addEventListener('click', () => {
                const product = productFromCard(card);
                const item = cart.get(product.id);
                if (item) updateQuantity(product.id, 1);
                else cart.set(product.id, { ...product, quantity: 1 });
                renderCart();
            }));

            const filterCards = () => {
                const query = search.value.trim().toLowerCase();
                let visible = 0;
                cards.forEach((card) => {
                    const matchesQuery = card.dataset.name.includes(query) || card.dataset.barcode.includes(query);
                    const matchesCategory = category === 'all' || (category === 'low' && Number(card.dataset.stock) <= 15) || (category === 'available' && Number(card.dataset.stock) > 15);
                    const show = matchesQuery && matchesCategory;
                    card.hidden = ! show;
                    if (show) visible++;
                });
                count.textContent = `${visible} ${visible === 1 ? 'producto' : 'productos'}`;
            };

            search.addEventListener('input', filterCards);
            scanButton.addEventListener('click', () => {
                barcodeInput.value = '';
                document.getElementById('scanner-focus-button')?.click();
                search.focus();
            });
            barcodeInput.addEventListener('input', () => {
                search.value = barcodeInput.value;
                filterCards();
                const matchingCard = cards.find((card) => card.dataset.barcode === barcodeInput.value);
                matchingCard?.querySelector('[data-add-product]').click();
            });
            document.querySelectorAll('[data-category]').forEach((button) => button.addEventListener('click', () => {
                category = button.dataset.category;
                document.querySelectorAll('[data-category]').forEach((item) => item.classList.toggle('is-active', item === button));
                filterCards();
            }));
        });
    </script>
</x-layouts::app>
