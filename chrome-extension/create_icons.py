from PIL import Image, ImageDraw, ImageFont
import os

# Create icons directory
os.makedirs('icons', exist_ok=True)

# Icon sizes
sizes = [16, 48, 128]

for size in sizes:
    # Create image with blue background
    img = Image.new('RGB', (size, size), color='#007bff')
    draw = ImageDraw.Draw(img)
    
    # Draw a camera icon (simple rectangle with circle)
    if size >= 48:
        # Camera body
        padding = size // 6
        draw.rectangle(
            [padding, padding + size//8, size - padding, size - padding],
            fill='white',
            outline='white'
        )
        # Lens
        center = size // 2
        radius = size // 4
        draw.ellipse(
            [center - radius, center - radius, center + radius, center + radius],
            fill='#007bff',
            outline='#007bff'
        )
    else:
        # Simple dot for small icon
        center = size // 2
        radius = size // 3
        draw.ellipse(
            [center - radius, center - radius, center + radius, center + radius],
            fill='white'
        )
    
    # Save
    img.save(f'icons/icon{size}.png')
    print(f'Created icon{size}.png')

print('\nAll icons created successfully!')
