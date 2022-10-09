// firstChild is the text of a div/span/td element
function getFirstChild(id) {
    var elem = document.getElementById(id);
    if (elem != null) {
        return elem.firstChild;
    }
}

function setNodeValue(elem, value) {
    if (elem != null) {
        elem.nodeValue = value;
    }
}

// use when saved elem is textnode, then can change class, etc
function setParentAttr(elem, attrName, attrValue) {
    if (elem != null) {
        elem.parentNode.setAttribute(attrName, attrValue);
    }
}

function createAttr(elem, attrName, attrValue) {
    var attr = document.createAttribute(attrName)
    attr.nodeValue = attrValue;
    elem.setAttributeNode(attr);
}

function removeAttr(elem, attrName) {
    elem.removeAttribute(attrName);
}

function focus(id) {
	document.getElementById('' + id).focus();
}

// focus and display item
function focusAndShow(focusid, showid) {
    focus(focusid);
    show(showid);
}

// Paging routines

// updates current page UI
function updatePagePositionDisplay(i) {
    document.getElementById('currentPage').firstChild.nodeValue = '' + i;
}