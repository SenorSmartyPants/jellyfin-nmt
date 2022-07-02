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
        elem.parentElement.setAttribute(attrName, attrValue);
    }
}

function createAttr(elem, attrName, attrValue) {
    var attr = document.createAttribute(attrName)
    attr.nodeValue = attrValue;
    elem.setAttributeNode(attr);
}