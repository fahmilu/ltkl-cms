// Custom script to change RichEditor toolbar button labels from H2/H3 to H3/H4
(function () {
    const processedButtons = new WeakSet();

    function updateRichEditorLabels() {
        // Find all RichEditor toolbars
        const toolbars = document.querySelectorAll(
            ".fi-fo-rich-editor-toolbar",
        );

        toolbars.forEach((toolbar) => {
            // Find all buttons in the toolbar
            const buttons = toolbar.querySelectorAll("button");

            buttons.forEach((button) => {
                // Skip if already processed
                if (processedButtons.has(button)) {
                    return;
                }

                const ariaLabel = button.getAttribute("aria-label") || "";
                const title = button.getAttribute("title") || "";
                const textContent = button.textContent.trim();
                const dataHeading = button.getAttribute("data-heading");
                const dataLevel = button.getAttribute("data-level");

                // Check if this is an H2 button (by data attributes, aria-label, title, or text)
                const isH2 =
                    dataHeading === "2" ||
                    dataLevel === "2" ||
                    ariaLabel.toLowerCase().includes("heading 2") ||
                    ariaLabel.toLowerCase().includes("h2") ||
                    title.toLowerCase().includes("heading 2") ||
                    title.toLowerCase().includes("h2") ||
                    textContent === "H2" ||
                    textContent === "Heading 2";

                // Check if this is an H3 button
                const isH3 =
                    dataHeading === "3" ||
                    dataLevel === "3" ||
                    ariaLabel.toLowerCase().includes("heading 3") ||
                    ariaLabel.toLowerCase().includes("h3") ||
                    title.toLowerCase().includes("heading 3") ||
                    title.toLowerCase().includes("h3") ||
                    textContent === "H3" ||
                    textContent === "Heading 3";

                if (isH2) {
                    // Update aria-label
                    if (ariaLabel) {
                        button.setAttribute(
                            "aria-label",
                            ariaLabel.replace(
                                /Heading 2|H2|heading 2|h2/gi,
                                "Heading 3",
                            ),
                        );
                    }
                    // Update title
                    if (title) {
                        button.setAttribute(
                            "title",
                            title.replace(
                                /Heading 2|H2|heading 2|h2/gi,
                                "Heading 3",
                            ),
                        );
                    }
                    // Update button text if it's visible text
                    if (textContent === "H2" || textContent === "Heading 2") {
                        button.textContent = "H3";
                    }
                    // Update any text elements inside (including SVG)
                    const allTextElements =
                        button.querySelectorAll("text, tspan, span");
                    allTextElements.forEach((textEl) => {
                        const content = textEl.textContent || "";
                        if (
                            content.includes("H2") ||
                            content.includes("Heading 2") ||
                            content.includes("h2")
                        ) {
                            textEl.textContent = content.replace(
                                /H2|Heading 2|h2/gi,
                                "H3",
                            );
                        }
                    });
                    processedButtons.add(button);
                }

                if (isH3) {
                    // Update aria-label
                    if (ariaLabel) {
                        button.setAttribute(
                            "aria-label",
                            ariaLabel.replace(
                                /Heading 3|H3|heading 3|h3/gi,
                                "Heading 4",
                            ),
                        );
                    }
                    // Update title
                    if (title) {
                        button.setAttribute(
                            "title",
                            title.replace(
                                /Heading 3|H3|heading 3|h3/gi,
                                "Heading 4",
                            ),
                        );
                    }
                    // Update button text if it's visible text
                    if (textContent === "H3" || textContent === "Heading 3") {
                        button.textContent = "H4";
                    }
                    // Update any text elements inside (including SVG)
                    const allTextElements =
                        button.querySelectorAll("text, tspan, span");
                    allTextElements.forEach((textEl) => {
                        const content = textEl.textContent || "";
                        if (
                            content.includes("H3") ||
                            content.includes("Heading 3") ||
                            content.includes("h3")
                        ) {
                            textEl.textContent = content.replace(
                                /H3|Heading 3|h3/gi,
                                "H4",
                            );
                        }
                    });
                    processedButtons.add(button);
                }
            });
        });
    }

    // Run when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", updateRichEditorLabels);
    } else {
        updateRichEditorLabels();
    }

    // Also run after Livewire updates (for dynamic content)
    if (window.Livewire) {
        Livewire.hook("morph.updated", () => {
            setTimeout(updateRichEditorLabels, 100);
        });

        Livewire.hook("morph.added", () => {
            setTimeout(updateRichEditorLabels, 100);
        });
    }

    // Use MutationObserver to watch for new RichEditor instances
    const observer = new MutationObserver(function (mutations) {
        let shouldUpdate = false;
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach((node) => {
                    if (
                        node.nodeType === 1 &&
                        (node.classList?.contains(
                            "fi-fo-rich-editor-toolbar",
                        ) ||
                            node.querySelector?.(".fi-fo-rich-editor-toolbar"))
                    ) {
                        shouldUpdate = true;
                    }
                });
            }
        });
        if (shouldUpdate) {
            setTimeout(updateRichEditorLabels, 50);
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
})();
