/**
 * Pseudo-representation of a Collection class.
 *
 * @author https://github.com/bretten
 */
function Collection() {
    // Holds the collection of objects
    this.objects = [];
}

/**
 * Sets the collection of objects.
 *
 * @param {Array}
 */
Collection.prototype.setSet = function(objects) {
    this.objects = objects;
}

/**
 * Gets the collection of objects.
 *
 * @returns {Array}
 */
Collection.prototype.getSet = function() {
    return this.objects;
}

/**
 * Adds an object to the collection.
 *
 * @param object
 * @returns {boolean}
 */
Collection.prototype.add = function(object) {
    if ((this.exists(object.id) > -1)) {
        return false;
    }
    this.objects.push(object);
    return true;
}

/**
 * Removes an object from the collection by id.
 *
 * @param object
 * @returns {boolean}
 */
Collection.prototype.remove = function(id) {
    var i = this.exists(id);
    if (i > -1) {
        this.objects.splice(i, 1);
        return true;
    }
    return false;
}

/**
 * Gets an object by id.
 *
 * @param id
 * @returns {*}
 */
Collection.prototype.get = function(id) {
    for (var i = 0; i < this.objects.length; i++) {
        if (id == this.objects[i].getID()) {
            return this.objects[i];
        }
    }
    return false;
}

/**
 * Checks if an object exists in the internal collection array.
 *
 * @param item
 * @returns {number}
 */
Collection.prototype.exists = function(item) {
    for (var i = 0; i < this.objects.length; i++) {
        if (item == this.objects[i].getID()) {
            return i;
        }
    }
    return -1;
}