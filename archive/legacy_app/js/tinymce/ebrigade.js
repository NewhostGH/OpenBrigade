// implementation of TinyMCE
// version 3.4.8
// inside eBrigade project
// with following options

tinyMCE.init({
    // General options
    mode : "exact",
    elements : "message",
    theme : "advanced",
    //skin : "o2k7",
    //skin_variant : "silver",
    plugins : "autolink,lists,pagebreak,style,layer,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave",

    // Theme options
    theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|fontselect,fontsizeselect,|,forecolor,backcolor",
    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,blockquote,|,undo,redo,|,link,unlink,",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_resizing : true,

    // Drop lists for link/image/media/template dialogs
    template_external_list_url : "lists/template_list.js",
    external_link_list_url : "lists/link_list.js",
    external_image_list_url : "lists/image_list.js",
    media_external_list_url : "lists/media_list.js"
});
