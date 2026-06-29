import $ from 'jquery';
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

// Ein- und ausblenden von Details
console.log("hier!");
console.log($);
$('#console').on('click', '.detailsTrigger', function() {
    const li = $(this).closest("li");
    const details = li.find('.details');

    $(this).toggleClass("open");
    details.slideToggle();
    return false;
});

// Einlesen von Posts
$('#information .start select[name="mode"]').change(function() {
    const input = $('#information .start input[name="amount"]');
    input.fadeToggle();
    input.prop('disabled', !input.prop('disabled'));
});

$('#information .start').submit(function() {
    const accountUid = $(this).attr("data-account");
    const mode = $(this).find('select[name="mode"] option:selected').val();
    const amount = Number($(this).find('input[name="amount"]').val());

    $('#information').slideUp();
    $('#console').slideDown();

    readPosts(accountUid, mode === "all" ? -1 : amount, 0);
    return false;
});

function readPosts(account, maxAmount, currentAmount, nextRequest) {
    const baseUri = TYPO3.settings.ajaxUrls.fpsocial_synchronize_deep;

    // Adds loading information to console.
    if (typeof nextRequest === "undefined") {
        const html = $("#blueprints .start").html();
        const li = $("<li class='current'>" + html + "</li>");
        $('#console > ul').append(li);
    } else {
        const html = $("#blueprints .next").html();
        const li = $("<li class='current'>" + html + "</li>");
        li.find('.uri').html(nextRequest);
        $('#console > ul').append(li);
    }

    // Generate Uri
    const uriParameters = {
        account: account
    }
    if (typeof nextRequest !== "undefined") {
        uriParameters.uri = encodeURIComponent(nextRequest);
    }

    new AjaxRequest(TYPO3.settings.ajaxUrls.fpsocial_synchronize_deep)
        .withQueryArguments(uriParameters)
        .get()
        .then(async function (response) {
            const resolved = await response.resolve();
            console.log(resolved);
            const posts = resolved.posts;

            // Anzeige anpassen
            const html = $('#blueprints .success').html();
            const li = $('#console li.current');
            li.html(html);
            li.find('.count').html(posts.length)
            for(let i = 0; i < posts.length; i++) {
                const post = posts[i];
                const postLi = $("<li>");
                for (const [key, value] of Object.entries(post)) {
                    postLi.append("<div><strong>" + key + ":</strong>" + value + "</div>");
                }
                li.find('.details').append(postLi)
            }
            li.removeClass("current");

            // Nächsten Aufruf ausführen oder Beenden
            if (
                (
                    typeof resolved.requests !== "undefined" &&
                    typeof resolved.requests.nextPage !== "undefined"
                ) && (
                    (maxAmount >= 0 && (currentAmount + resolved.posts.length) < maxAmount) ||
                    maxAmount < 0
                )
            ) {
                readPosts(account, maxAmount, currentAmount + resolved.posts.length, resolved.requests.nextPage);
            } else {
                $('#success').slideDown();
            }
        })
        .catch(async function(response) {
            let message = "";
            let code = "";

            if(typeof response.message !== "undefined") {
                message = response.message;
            } else {
                const resolved = await response.resolve();
                message = resolved.errorMessage;
                code = resolved.errorCode;
            }

            // Anzeige anpassen
            const html = $($('#blueprints .error').html());
            html.find('.code').html(code);
            html.find('.message').html(message);

            const current = $('#console li.current');
            if(current.length) {
                current.html(html).removeClass("current");
            } else {
                const newLi = $("<li>");
                newLi.html(html);
                $('#console ul').append(newLi);
            }

            // Fehlermeldung ausgeben
            $('#error').slideDown();
        });
}