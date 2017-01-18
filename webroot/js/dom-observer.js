var dom_observer = dom_observer || {};

(function () {
    /**
     * Observe DOM Logic.
     */
    function observeDOM()
    {
        this.MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
    }

    observeDOM.prototype = {
        /**
         * Method that triggers callback function if an element was added to the target DOM element.
         *
         * @param {object} obj Target DOM element
         * @param {Function} callback Callback function
         * @return {undefined}
         */
        added: function (obj, callback) {
            if (this.MutationObserver) {
                // define a new observer
                var obs = new this.MutationObserver(function (mutations, observer) {
                    if (mutations[0].addedNodes.length) {
                        callback(mutations[0].addedNodes);
                    }
                });
                // have the observer observe for changes in children
                obs.observe(obj, {
                    childList: true,
                    subtree: true
                });
            }
        },

        /**
         * Method that triggers callback function if an element was removed from the target DOM element.
         *
         * @param {object} obj Target DOM element
         * @param {Function} callback Callback function
         * @return {undefined}
         */
        removed: function (obj, callback) {
            if (this.MutationObserver) {
                // define a new observer
                var obs = new this.MutationObserver(function (mutations, observer) {
                    if (mutations[0].removedNodes.length) {
                        callback(mutations[0].removedNodes);
                    }
                });
                // have the observer observe for changes in children
                obs.observe(obj, {
                    childList: true,
                    subtree: true
                });
            }
        }
    };

    dom_observer = new observeDOM();
})();