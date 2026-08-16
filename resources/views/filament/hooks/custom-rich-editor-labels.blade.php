<script>
(function() {
    function updateRichEditorLabels() {
        const toolbars = document.querySelectorAll('.fi-fo-rich-editor-toolbar');
        
        toolbars.forEach(toolbar => {
            const buttons = toolbar.querySelectorAll('button.fi-fo-rich-editor-tool');
            
            buttons.forEach(button => {
                if (button.dataset.labelAdded) return;
                
                const onClick = button.getAttribute('x-on:click') || '';
                
                // H2 button -> add "H3" label inside button and hide icon
                if (onClick.includes('level: 2') || onClick.includes('level:2')) {
                    // Hide the SVG icon inside the button
                    const icon = button.querySelector('svg.fi-icon');
                    if (icon) {
                        icon.style.display = 'none';
                    }
                    
                    // Add label inside the button
                    const label = document.createElement('span');
                    label.textContent = 'H3';
                    label.style.cssText = 'font-size: 12px; font-weight: 600; color: white;';
                    button.appendChild(label);
                    
                    button.dataset.labelAdded = 'true';
                }
                
                // H3 button -> add "H4" label inside button and hide icon
                if (onClick.includes('level: 3') || onClick.includes('level:3')) {
                    // Hide the SVG icon inside the button
                    const icon = button.querySelector('svg.fi-icon');
                    if (icon) {
                        icon.style.display = 'none';
                    }
                    
                    // Add label inside the button
                    const label = document.createElement('span');
                    label.textContent = 'H4';
                    label.style.cssText = 'font-size: 12px; font-weight: 600; color: white;';
                    button.appendChild(label);
                    
                    button.dataset.labelAdded = 'true';
                }
            });
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateRichEditorLabels);
    } else {
        updateRichEditorLabels();
    }
    
    if (window.Livewire) {
        Livewire.hook('morph.updated', () => setTimeout(updateRichEditorLabels, 100));
        Livewire.hook('morph.added', () => setTimeout(updateRichEditorLabels, 100));
    }
    
    const observer = new MutationObserver(() => {
        if (document.querySelector('.fi-fo-rich-editor-toolbar')) {
            setTimeout(updateRichEditorLabels, 50);
        }
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
})();
</script>
