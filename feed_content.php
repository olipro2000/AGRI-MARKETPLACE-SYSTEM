        <!-- Products Grid -->
        <div class="products-grid">
            <?php foreach($products as $product): ?>
            <div class="product-card" 
                 style="width:100%;border-radius:16px;overflow:hidden;background:#fff;
                        box-shadow:0 2px 10px rgba(0,0,0,0.07);font-family:Arial, sans-serif;">

                <div class="product-image" 
                     style="width:100%;height:230px;">
                    <?php if($product['main_image']): ?>
                        <img src="/curuzamuhinzi/uploads/products/<?= htmlspecialchars($product['main_image']) ?>" 
                             alt="<?= htmlspecialchars($product['product_name']) ?>"
                             style="width:100%;height:100%;object-fit:cover;display:block;">
                    <?php endif; ?>
                </div>

                <div class="product-info" style="padding:16px;">

                    <h3 class="product-name" 
                        style="font-size:20px;margin-bottom:6px;">
                        <?= htmlspecialchars($product['product_name']) ?>
                    </h3>

                    <div class="product-price" 
                         style="font-size:18px;font-weight:bold;color:#00a651;margin-bottom:12px;">
                        <?= number_format($product['price']) ?> RWF
                    </div>

                    <div class="product-seller" 
                         style="display:flex;align-items:center;margin-bottom:16px;gap:10px;">

                        <div class="seller-avatar" 
                             style="width:42px;height:42px;border-radius:50%;overflow:hidden;">
                            <?php if($product['profile_picture']): ?>
                                <img src="/curuzamuhinzi/uploads/profiles/<?= htmlspecialchars($product['profile_picture']) ?>" 
                                     alt=""
                                     style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%;height:100%;background:#00a651;color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                                    <?= strtoupper(substr($product['first_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="seller-info" style="display:flex;flex-direction:column;">
                            <div class="seller-name"><?= htmlspecialchars($product['first_name'] . ' ' . $product['last_name']) ?></div>
                            <div class="seller-location"><?= htmlspecialchars($product['district'] . ', ' . $product['province']) ?></div>
                        </div>
                    </div>

                    <div class="product-actions" 
                         style="display:flex;gap:10px;margin-top:10px;">

                        <button class="action-btn btn-primary" 
                                style="flex:1;padding:10px 0;border-radius:10px;border:none;
                                       cursor:pointer;font-weight:600;background:#00a651;color:white;">
                            💬 Contact
                        </button>

                        <button class="action-btn btn-secondary wishlist-btn" 
                                style="flex:1;padding:10px 0;border-radius:10px;border:none;
                                       cursor:pointer;font-weight:600;background:#f5f5f5;color:#d40055;">
                            ❤️ Save
                        </button>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>