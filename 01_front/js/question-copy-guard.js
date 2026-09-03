document.addEventListener("copy", function (event) {

    const selection = window.getSelection();

    if (!selection || selection.isCollapsed) {
        return;
    }

    const selectedText = selection.toString();

    if (selectedText.length <= 100) {
        return;
    }

    const anchorElement =
        selection.anchorNode?.nodeType === Node.TEXT_NODE
            ? selection.anchorNode.parentElement
            : selection.anchorNode;

    const focusElement =
        selection.focusNode?.nodeType === Node.TEXT_NODE
            ? selection.focusNode.parentElement
            : selection.focusNode;

    const insideQuestion =
        anchorElement?.closest?.(".question-copy-zone") ||
        focusElement?.closest?.(".question-copy-zone");

    if (!insideQuestion) {
        return;
    }

    event.preventDefault();

    alert(
        "問題文は100文字までコピーできます。\n" +
        "問題全体のコピーはできません。"
    );
});