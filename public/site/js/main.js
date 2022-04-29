$(document).ready(function () {
    //upload images
    $(function () {
        var imagesPreview = function (input, placeToInsertImagePreview) {
            $(".gallery").empty();
            if (input.files) {
                var filesAmount = input.files.length;
                for (i = 0; i < filesAmount; i++) {
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        var image = document.createElement("img");
                        image.setAttribute("src", event.target.result);
                        var body = document.createElement("div");
                        var button = document.createElement("button");
                        var input = document.createElement("input");
                        input.setAttribute("name", "images[]");
                        input.setAttribute("type", "hidden");
                        button.setAttribute("class", "close");
                        button.innerHTML = '<i class="fa fa-times-circle"></i>';
                        body.appendChild(image);
                        body.appendChild(input);
                        body.appendChild(button);
                        body.setAttribute("class", "images");
                        console.log(body);
                        $(".gallery").append(body);
                        $($.parseHTML(body)).appendTo(
                            placeToInsertImagePreview
                        );
                    };
                    reader.readAsDataURL(input.files[i]);
                }
            }
        };
        $(document).on("click", ".close", function (event) {
            event.preventDefault();
            $(this).parent().remove();
        });
        $("#gallery-photo-add").on("change", function () {
            imagesPreview(this, "div.gallery");
        });
    });

    $(".del-cart").click(function () {
        event.preventDefault();
        $(this).parent().remove();
    });
    //delete address
    $(".delete-address").click(function () {
        event.preventDefault();
        $(this).parents(".address-item").remove();
    });

    $(".sign-upscrol").click(function () {
        $(this).parents(".modal-body").find(".fade-in").slideDown();
        $(this).parents(".modal-body").find(".fade-up").slideUp();
    });
    $(".sign-inscrol").click(function () {
        $(this).parents(".modal-body").find(".fade-up").slideDown();
        $(this).parents(".modal-body").find(".fade-in").slideUp();
    });

    $(".forget-link").click(function () {
        $(this).parents(".modal-body").find(".fade-pass").slideDown();
        $(this).parents(".modal-body").find(".fade-up").slideUp();
    });

    //lang
    $(".open-lang").click(function () {
        event.preventDefault();
        $(".lang-div").addClass("open-langdiv");
        $(".lang-over").addClass("w-100");
    });
    $(".lang-over , .close-lang").click(function () {
        $(".lang-div").removeClass("open-langdiv");
        $(".lang-over").removeClass("w-100");
    });
    //grid
    $(".line").click(function () {
        event.preventDefault();
        $(".feature-pro .pro-cart").addClass("line-s");
        $(".feature-pro .row").addClass("flex-column");
        $(".feature-pro .row .col-lg-4").css("max-width", "100%");
    });

    $(".grid").click(function () {
        event.preventDefault();
        $(".feature-pro .pro-cart").removeClass("line-s");
        $(".feature-pro .row").removeClass("flex-column");
        $(".feature-pro .row .col-lg-4").removeAttr("style");
    });
    //add-address
    $(".add-address , .edit-address").click(function () {
        event.preventDefault();
        $(".address-form").slideDown();
        $(this).parents(".my-address").slideUp();
    });
    $(".acc-tabs.nav-tabs .nav-link").click(function () {
        $(".address-form").slideUp();
        $(".add-address , .edit-address").parents(".my-address").slideDown();
    });

    //del-tr
    $(".del-tr").click(function () {
        $(this).parents("tr").remove();
    });

    //filter
    $(".filter").click(function () {
        event.preventDefault();
        $(this).parents(".filter-div").siblings(".fliter-form").slideToggle();
        $(this).parent().find(".fa-angle-down").toggleClass("rot180");
    });

    $(".remove-v").click(function () {
        $(this).parent().remove();
    });
    //change lang
    $(".lang-change").click(function () {
        if (document.documentElement.lang.toLowerCase() === "en") {
            $("html").removeAttr("lang");
        } else {
            $("html").attr("lang", "en");
        }
    });

    //mobile na
    $(".overlay").fadeOut();

    $(".mob-collaps").click(function () {
        $(this).parents("header").find("nav").toggleClass("nav-open");
        $(".overlay").fadeToggle();
        $(this).find("span:first-child").toggleClass("rotate");
        $(this).find("span:nth-child(2)").toggleClass("none");
        $(this).find("span:nth-child(3)").toggleClass("rotate2");
    });

    $(".overlay").click(function () {
        $("nav.site-nav").removeClass("nav-open");
        $(this).fadeOut();
        $(".mob-collaps span:first-child").removeClass("rotate");
        $(".mob-collaps span:nth-child(2)").removeClass("none");
        $(".mob-collaps span:nth-child(3)").removeClass("rotate2");
    });
    //   $('.add-wishlist , .add-like').click(function () {
    //     event.preventDefault();
    //     $(this).find('i').toggleClass('fas red');
    //   });
    //input number
    var minVal = 1,
        maxVal = 20; // Set Max and Min values
    // Increase product quantity on cart page
    $(".increaseQty").on("click", function () {
        var $parentElm = $(this).parents(".qtySelector");
        $(this).addClass("clicked");
        setTimeout(function () {
            $(".clicked").removeClass("clicked");
        }, 100);
        var value = $parentElm.find(".qtyValue").val();
        if (value < maxVal) {
            value++;
        }
        $parentElm.find(".qtyValue").val(value);
    });
    // Decrease product quantity on cart page
    $(".decreaseQty").on("click", function () {
        var $parentElm = $(this).parents(".qtySelector");
        $(this).addClass("clicked");
        setTimeout(function () {
            $(".clicked").removeClass("clicked");
        }, 100);
        var value = $parentElm.find(".qtyValue").val();
        if (value > 1) {
            value--;
        }
        $parentElm.find(".qtyValue").val(value);
    });
});

//loader
$(window).on("load", function () {
    $(".loader").fadeOut(2000, function () {
        $("body").fadeIn(1000);
    });
});

//product-slider
var galleryThumbs = new Swiper(".gallery-thumbs", {
    spaceBetween: 10,
    slidesPerView: 4,
    loop: true,
    freeMode: true,
    loopedSlides: 5, //looped slides should be the same
    watchSlidesVisibility: true,
    watchSlidesProgress: true,
});
var galleryTop = new Swiper(".gallery-top", {
    spaceBetween: 10,
    loop: true,
    loopedSlides: 5, //looped slides should be the same
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    thumbs: {
        swiper: galleryThumbs,
    },
});

$("#product-details").on("shown.bs.modal", function () {
    galleryTop.update();
    galleryThumbs.update();
});

//map
function initialize() {
    var myLatlng = new google.maps.LatLng(24.7135517, 46.67529569999999);

    var myOptions = {
        zoom: 7,
        center: myLatlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
    };

    var map = new google.maps.Map(
        document.getElementById("add_map"),
        myOptions
    );

    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        draggable: true,
    });

    var searchBox = new google.maps.places.SearchBox(
        document.getElementById("pac-input")
    );
    google.maps.event.addListener(searchBox, "places_changed", function () {
        var places = searchBox.getPlaces();
        var bounds = new google.maps.LatLngBounds();
        var i, place;
        for (i = 0; (place = places[i]); i++) {
            bounds.extend(place.geometry.location);
            marker.setPosition(place.geometry.location);
        }
        map.fitBounds(bounds);
        map.setZoom(12);
    });

    google.maps.event.addListener(marker, "position_changed", function () {
        var lat = marker.getPosition().lat();
        var lng = marker.getPosition().lng();
        $("#lat").val(lat);
        $("#lng").val(lng);
    });
}

// price-range

var lowerSlider = document.querySelector("#lower");
var upperSlider = document.querySelector("#upper");

document.querySelector("#two").value = upperSlider.value;
document.querySelector("#one").value = lowerSlider.value;

var lowerVal = parseInt(lowerSlider.value);
var upperVal = parseInt(upperSlider.value);

upperSlider.oninput = function () {
    lowerVal = parseInt(lowerSlider.value);
    upperVal = parseInt(upperSlider.value);

    if (upperVal < lowerVal + 4) {
        lowerSlider.value = upperVal - 4;
        if (lowerVal == lowerSlider.min) {
            upperSlider.value = 4;
        }
    }
    document.querySelector("#two").value = this.value;
};

lowerSlider.oninput = function () {
    lowerVal = parseInt(lowerSlider.value);
    upperVal = parseInt(upperSlider.value);
    if (lowerVal > upperVal - 4) {
        upperSlider.value = lowerVal + 4;
        if (upperVal == upperSlider.max) {
            lowerSlider.value = parseInt(upperSlider.max) - 4;
        }
    }
    document.querySelector("#one").value = this.value;
};
