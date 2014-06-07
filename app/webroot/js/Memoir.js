/**
 * Pseudo-representation of an Memoir class.
 *
 * @author https://github.com/bretten
 */
function Memoir(id, title, file, message, order) {
    this.id = id;
    this.title = title;
    this.file = file;
    this.message = message;
    this.order = order;
}

/**
 * Sets the id.
 *
 * @param int
 */
Memoir.prototype.setID = function(id) {
    this.id = id;
}

/**
 * Gets the id.
 *
 * @returns int
 */
Memoir.prototype.getID = function() {
    return this.id;
}

/**
 * Sets the title.
 *
 * @param string
 */
Memoir.prototype.setTitle = function(title) {
    this.title = title;
}

/**
 * Gets the title.
 *
 * @returns string
 */
Memoir.prototype.getTitle = function() {
    return this.title;
}

/**
 * Sets the file.
 *
 * @param string
 */
Memoir.prototype.setFile = function(file) {
    this.file = file;
}

/**
 * Gets the file.
 *
 * @returns string
 */
Memoir.prototype.getFile = function() {
    return this.file;
}

/**
 * Sets the message.
 *
 * @param string
 */
Memoir.prototype.setMessage = function(message) {
    this.file = message;
}

/**
 * Gets the message.
 *
 * @returns string
 */
Memoir.prototype.getMessage = function() {
    return this.message;
}

/**
 * Sets the order.
 *
 * @param string
 */
Memoir.prototype.setOrder = function(order) {
    this.file = order;
}

/**
 * Gets the order.
 *
 * @returns string
 */
Memoir.prototype.getOrder = function() {
    return this.order;
}