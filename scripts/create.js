document.addEventListener("DOMContentLoaded", function() {
    // Image upload preview
    const imageInput = document.getElementById('product_image');
    const imagePreview = document.getElementById('image-preview');
    
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Hide the placeholder icon and text
                imagePreview.querySelector('i').style.display = 'none';
                imagePreview.querySelector('span').style.display = 'none';
                
                // Create or show the image element
                let img = imagePreview.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    imagePreview.appendChild(img);
                }
                
                img.src = e.target.result;
                img.style.display = 'block';
            }
            
            reader.readAsDataURL(file);
        }
    });
    
    // Allow clicking on the preview to trigger file selection
    imagePreview.addEventListener('click', function() {
        imageInput.click();
    });
    
    // Form validation
    const form = document.getElementById('product-form');
    form.addEventListener('submit', function(e) {
        // Validate selling price is higher than buying price
        const buyingPrice = parseFloat(document.getElementById('buying_price').value);
        const sellingPrice = parseFloat(document.getElementById('selling_price').value);
        
        if (sellingPrice <= buyingPrice) {
            alert('Selling price must be higher than buying price');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
    // Auto-format price inputs
    const priceInputs = document.querySelectorAll('input[type="number"][step="0.01"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !isNaN(this.value)) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    });
});