function swalAlert( message ) {
    swal(message,
        {addButton: 0},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.swal2-confirm').on('click', function() {
        closeSwal();
    });
}

function getLogo(className) {
    type = className.split('-');

    logo = '<span>';
    if (type[1] == 'error') {
        logo += '<span class="swal2-x-mark-line-left"></span>\
                <span class="swal2-x-mark-line-right"></span>';
    }
    if (type[1] == 'success') {
        logo += '<span class="swal2-success-line-tip"></span>\
                <span class="swal2-success-line-long"></span>';
    }
    if (type[1] == 'info')
        logo += '<span class="swal2-icon-content swal2-info-line">!</span>';
    logo += "</span>";
    return logo;
}

function swal(message, addElement = {addButton : 0}, editSwalElement = {disableButton: 0}) {

    swalObject = '<div class="swal2-container swal2-center swal2-backdrop-show" style="overflow-y: auto;">\
        <div aria-labelledby="swal2-title" aria-describedby="swal2-content" class="swal2-popup swal2-modal swal2-icon-error swal2-show" tabindex="-1" role="dialog" aria-live="assertive" aria-modal="true" style="display: flex;">\
            <div class="swal2-header">\
                <ul class="swal2-progress-steps" style="display: none;"></ul>';
    if (editSwalElement.class != null) {
        // edit Swal default element 
        // write your class exemple editSwalElement.class = "icon swal2-success"
        // editSwalElement = {class, style, text}

        swalObject += '\
            <div class="swal2-'+editSwalElement.class+' swal2-icon-show" style="display: '+editSwalElement.style+';">';
        if (editSwalElement.class == 'title')
            swalObject += editSwalElement.text;
        else
            swalObject += getLogo(editSwalElement.class);
        swalObject += '</div>';
    } else {
        swalObject += '\
            <div class="swal2-icon swal2-error swal2-icon-show" style="display: flex;">\
                <span class="swal2-x-mark">\
                    <span class="swal2-x-mark-line-left"></span>\
                    <span class="swal2-x-mark-line-right"></span>\
                </span>\
            </div>'
    }
    if (editSwalElement.disableButton == 0)
    swalObject += '<button type="button" class="swal2-close" style="display: "block";" aria-label="Fermer la fenêtre">×</button>';

    swalObject += '</div>\
        <div class="swal2-content">\
            <div id="swal2-content" class="swal2-html-container" style="display: block;">'+message+'</div>\
            <input class="swal2-input" style="display: none;">\
            <input type="file" class="swal2-file" style="display: none;">\
            <div class="swal2-range" style="display: none;">\
                <input type="range"><output></output>\
            </div>\
            <div class="swal2-validation-message" id="swal2-validation-message"></div>\
        </div>\
        <div class="swal2-actions">';

        if (addElement.html) {
            swalObject = swalObject + addElement.html;
        }
        else{
            if (addElement.addButton == 1) {
                if (addElement.addButton) {}
                swalObject = swalObject + '<button type="button" class="font-weight-bold btn-swal '+addElement.classButton+'" style="display: inline-block;" aria-label="">'+addElement.textButton+'</button>';
            }

        }
        
        if (editSwalElement.disableButton == 0)
            swalObject = swalObject + '<button type="button" class="font-weight-bold btn-swal btn-secondary swal2-confirm" aria-label="">Fermer</button>\
                </div>\
            </div>\
            </div>\
        </div>';


    $('body').append(swalObject);   

    $('.swal2-close').on('click', function(){
        closeSwal();
    })
    $('.swal2-confirm').on('click', function(){
        closeSwal();
    })

};

function closeSwal(){
    $( ".swal2-container" ).fadeOut('fast');
}
