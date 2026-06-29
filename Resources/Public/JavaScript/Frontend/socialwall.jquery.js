(function( $ ) {

    $.fn.socialwall = function () {
        const $this = $(this[0]);
        let referenceRecords;

        /**
         * reads newest and oldest records from the wall to get reference posts for an update
         */
        const readReferenceRecords = () => {
            // reset reference, because a reference could be replaced
            referenceRecords = {};
            $this.find('.post[data-source="collection"]').each(function() {
                readReferenceRecord($(this));
            });
        }

        /**
         * processes a single record to check if this could be a reference posts for newest or oldest posts.
         * @param record
         */
        const readReferenceRecord = (record) => {
            // check source
            const realRecord = record.hasClass("post") ? record : record.find('.post');
            const source = realRecord.attr("data-source");
            if(source !== "collection") return;

            // get identifier
            const identifier = realRecord.attr("data-identifier");
            const identifierParts = identifier.split(":");
            const recordType = identifierParts[0];

            // get timestamp
            const timestamp = realRecord.attr("data-timestamp");

            // create map index if not existing
            if(typeof referenceRecords[recordType] === "undefined") {
                referenceRecords[recordType] = {};
            }

            // check if this is the oldest or newest post
            if(typeof referenceRecords[recordType]["newest"] === "undefined" || referenceRecords[recordType]["newest"]["timestamp"] < timestamp) {
                referenceRecords[recordType]["newest"] = {
                    identifier : identifier,
                    timestamp : timestamp
                };
            }
            if(typeof referenceRecords[recordType]["oldest"] === "undefined" || referenceRecords[recordType]["oldest"]["timestamp"] > timestamp) {
                referenceRecords[recordType]["oldest"] = {
                    identifier : identifier,
                    timestamp : timestamp
                };
            }
        }

        const loadOlderPosts = (button, url) => {
            disableButton(button);

            const contentObjectUid = $this.attr("data-cuid");
            const columns = $this.attr("data-columns");
            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                data : {
                    tx_fpsocial_ajax : {
                        referenceRecords : referenceRecords,
                        contentObjectUid : contentObjectUid,
                        amount : columns
                    }
                },
                success: function(response) {
                    if(response.amount) {
                        // append posts to wall
                        response.records.forEach((renderedRecord) => {
                            const domElement = $(renderedRecord);
                            readReferenceRecord(domElement);
                            $this.find('.posts').append(domElement);
                        });
                        if(typeof fpscocial_wall_afterchange !== "undefined") {
                            fpscocial_wall_afterchange($this);
                        }
                        enableButton(button);
                    } else {
                        hideButton(button);
                    }
                }
            });
        }

        const loadNewerPosts = () => {
            const url = $this.attr("data-load-newer-url");
            const replace = $this.attr("data-load-newer-replace") === "1";
            const cUid = $this.attr("data-cuid");
            const columns = $this.attr("data-columns");

            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                data : {
                    tx_fpsocial_ajax : {
                        referenceRecords : referenceRecords,
                        cUid : cUid,
                        amount : columns
                    }
                },
                success: function(response) {
                    if(response.amount) {
                        // append posts to wall
                        response.records.forEach((renderedRecord) => {
                            const domElement = $(renderedRecord);
                            readReferenceRecord(domElement);
                            domElement.hide();
                            $this.find('.posts').prepend(domElement);

                            // add new record
                            const delay = Math.random()*1000;
                            domElement.delay(delay).fadeIn();

                            // remove old one?
                            if(replace) {
                                const lastRecord = $this.find('.posts .post[data-source="collection"]').last();
                                lastRecord.parent().remove();
                            }
                        });
                        if(replace) {
                            readReferenceRecords();
                        }
                        if(typeof fpscocial_wall_afterchange !== "undefined") {
                            fpscocial_wall_afterchange($this);
                        }
                    }
                    setTimeout(loadNewerPosts, 5000);
                }
            });
        }

        const disableButton = (button) => {
            button.find('.inactive').hide();
            button.find('.active').show();
            button.addClass("disabled").prop("disabled", true);
        }

        const enableButton = (button) => {
            button.find('.inactive').show();
            button.find('.active').hide();
            button.removeClass("disabled").prop("disabled", false);
        }

        const hideButton = (button) => {
            button.fadeOut();
        }

        // load data on init
        readReferenceRecords($this);

        // listener to load older posts
        $this.find('a[data-action="load-older"]').click(function() {
            const href = $(this).attr("href");
            loadOlderPosts($(this), href);
            return false;
        });

        // event to load newer posts
        const loadNewer = $this.attr("data-load-newer") === "1";
        if(loadNewer) {
            setTimeout(loadNewerPosts, 5000);
        }
    }

    return this;
}( jQuery ));