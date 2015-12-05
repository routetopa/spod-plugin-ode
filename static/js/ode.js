/* ODE PLUGIN JS */

ODE = {};

ODE.init = function()
{
    ComponentService.deep_url = ODE.deep_url;

    window.addEventListener('generic-cards-container-controllet_card-selected', function(e){

        var fields = e.detail.selectedElement.getAttribute("fields");

        var data = {detail:{data:{datalet    : e.detail.selectedElement.getAttribute("datalet"),
                                  fields     : fields.substring(1, fields.length-1).split('","'),
                                  params     : JSON.parse(e.detail.selectedElement.getAttribute("preset")),
                                  staticData : e.detail.selectedElement.getAttribute("static-data")}}};

        ODE.savedDataletListener(data);

    });

    $('#open_window_button').click(function (e){
        ODE.pluginPreview = 'newsfeed';
    });

};

ODE.addOdeOnComment = function()
{
    var ta = $('.ow_comments_input textarea');
    $.each(ta, function(idx, obj) {
        if ( $(obj).attr('data-preview-added') ) {
            return;
        } else {
            $(obj).attr('data-preview-added', true);
        }
        var id = obj.id;

        // Add ODE on Comment
        var odeElem = $(obj).parent().find('.ow_attachments').first().prepend($('<a href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'ic_lens.svg) no-repeat center;" data-id="' + id + '"></a>'));
        odeElem = odeElem.children().first();
        odeElem.click(function (e) {
            ODE.pluginPreview = 'comment';
            ODE.commentTarget = e.target;
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {text:'testo'} , {width:'90%', height:'80vh', iconClass:'ow_ic_lens', title:''});
        });

        // Add PRIVATE_ROOM on Comment
        if(ODE.is_private_room_active)
        {
            var prElem = $(obj).parent().find('.ow_attachments').first().prepend($('<a href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'ic_attach.svg) no-repeat center;" data-id="' + id + '"></a>'));
            prElem = prElem.children().first();
            prElem.click(function (e) {
                ODE.pluginPreview = 'comment';
                ODE.commentTarget = e.target;
                $('.ow_submit_auto_click').show();
                document.getElementById('share_from_private_room').dispatchEvent(new Event('animated-button-container-controllet_open-window'));
            });
        }

    });
};

// Listen for datalet event
ODE.savedDataletListener = function(e)
{
    var data = e.detail.data;
    ODE.setDataletValues(data);

    switch(ODE.pluginPreview)
    {

        case 'newsfeed' :
            $('#ode_controllet_placeholder').slideToggle('fast');
            ODE.loadDatalet(data.datalet, data.params, data.fields, data.staticData, 'ode_controllet_placeholder');
            break;

        case 'public-room' :
        case 'comment' :
            $(ODE.commentTarget).parent().first().prepend($('<a class="ode_done" style="background: url(' + ODE.THEME_IMAGES_URL + 'ic_ok_gray.svg) no-repeat center;"></a>'));
            break;

        case 'event' :
        case 'forum' :
            $('.ode_done').first().append($('<div class="ode_done" style="background:url(' + ODE.THEME_IMAGES_URL + 'ic_ok_gray.svg) no-repeat center; height:20px; width:20px; float:left"></div>'));
            break;

        case 'private-room' :
            ODE.privateRoomDatalet();
            break;

        default : break;

    }

    if(typeof previewFloatBox != 'undefined')
        previewFloatBox.close();
};

ODE.privateRoomDatalet = function ()
{
    if(SPODPR.dataletOpened == undefined || SPODPR.cardOpened == undefined)
    {
        delete ODE.dataletParameters['dataletId'];
        delete ODE.dataletParameters['cardId'];
    }
    else
    {
        $.extend(ODE.dataletParameters, {dataletId: SPODPR.dataletOpened, cardId: SPODPR.cardOpened});
    }

    $.ajax({
        type: 'post',
        url: ODE.ajax_private_room_datalet,
        data: ODE.dataletParameters,
        dataType: 'JSON',
        success: function(data){
            previewFloatBox.close();

            if(ODE.dataletParameters.cardId == undefined)
                add_card(ODE.dataletParameters,data.id);
            else
                replace_datalet_card(ODE.dataletParameters, SPODPR.cardOpened);
        },
        error: function( XMLHttpRequest, textStatus, errorThrown ){
            OW.error(textStatus);
        },
        complete: function(){}
    });
};

ODE.setDataletValues = function (data)
{
    $('input[name=ode_datalet]').val(data.datalet);
    $('input[name=ode_fields]').val('"'+data.fields.join('","')+'"');
    $('input[name=ode_params]').val(JSON.stringify(data.params));
    $('input[name=ode_data]').val(data.staticData);

    ODE.dataletParameters.component = data.datalet;
    ODE.dataletParameters.params    = JSON.stringify(data.params);
    ODE.dataletParameters.fields    = '"'+data.fields.join('","')+'"';
    ODE.dataletParameters.data      = data.staticData;
    ODE.dataletParameters.comment   = data.params.description;
    ODE.dataletParameters.title     = data.params.title;
};

ODE.loadDatalet = function(component, params, fields, cache, placeholder)
{
    $.extend(params, {data:cache});

    ComponentService.getComponent({
        component   : component,
        params      : params,
        fields      : fields,
        placeHolder : placeholder
    });
};

ODE.odeLoadNewItem = function(params, preloader, id, callback)
{
    var self = window.ow_newsfeed_feed_list[id];

    if ( typeof preloader == 'undefined' )
    {
        preloader = true;
    }

    if (preloader)
    {
        var $ph = self.getPlaceholder();
        this.$listNode.prepend($ph);
    }
    this.loadItemMarkup(id, params, function($a) {
        this.$listNode.prepend($a.hide());

        if ( callback )
        {
            callback.apply(self);
        }

        self.adjust();
        if ( preloader )
        {
            var h = $a.height();
            $a.height($ph.height());
            $ph.replaceWith($a.css('opacity', '0.1').show());
            $a.animate({opacity: 1, height: h}, 'fast');
        }
        else
        {
            $a.animate({opacity: 'show', height: 'show'}, 'fast');
        }
    });

};

ODE.loadItemMarkup = function(id, params, callback)
{
    var self = window.ow_newsfeed_feed_list[id];

    params.feedData = self.data;
    params.cycle = params.cycle || {lastItem: false};

    params = JSON.stringify(params);

    NEWSFEED_Ajax(window.ODE.ajax_load_item, {p: params}, function( markup ) {

        if ( markup.result == 'error' )
        {
            return false;
        }

        var $m = $(markup.html);
        callback.apply(self, [$m]);
        OW.bindAutoClicks($m);

        self.processMarkup(markup);
    });
};

ODE.dataletParameters =
{
    component:'',
    params:'',
    fields:'',
    data:'',
    comment:''
};

ODE.commentSendMessage = function(message, context)
{
    var self = context;

    if(self.pluginKey == "spodpublic")
    {
        //1 neutral - 2 up - 3 down
        var sentiment = $("#comment_sentiment_"+self.entityId).attr('sentiment');
    }

    var dataToSend = {
        entityType: self.entityType,
        entityId: self.entityId,
        displayType: self.displayType,
        pluginKey: self.pluginKey,
        ownerId: self.ownerId,
        cid: self.uid,
        attchUid: self.attchUid,
        commentCountOnPage: self.commentCountOnPage,
        commentText: message,
        initialCount: self.initialCount,
        datalet: ODE.dataletParameters,
        plugin: ODE.pluginPreview,
        publicRoom: (typeof parent.ODE.publicRoom === 'undefined') ? '' : parent.ODE.publicRoom,
        sentiment: (typeof sentiment === 'undefined') ? '' : sentiment
    };

    if( self.attachmentInfo ){
        dataToSend.attachmentInfo = JSON.stringify(self.attachmentInfo);
    }
    else if( self.oembedInfo ){
        dataToSend.oembedInfo = JSON.stringify(self.oembedInfo);
    }

    $.ajax({
        type: 'post',
        //url: self.addUrl,
        url: ODE.ajax_add_comment,
        data: dataToSend,
        dataType: 'JSON',
        success: function(data){
            self.repaintCommentsList(data);
            OW.trigger('base.photo_attachment_uid_update', {uid:self.attchUid, newUid:data.newAttachUid});
            self.eventParams.commentCount = data.commentCount;
            OW.trigger('base.comment_added', self.eventParams);
            self.attchUid = data.newAttachUid;

            self.$formWrapper.removeClass('ow_preloader');
            self.$commentsInputCont.show();

            /* ODE */
            // Remove ic_ok icon from comment field
            $(ODE.commentTarget).parent().find('.ode_done').remove();
            ODE.commentTarget = null;
            ODE.reset();
            /* ODE */

        },
        error: function( XMLHttpRequest, textStatus, errorThrown ){
            OW.error(textStatus);
        },
        complete: function(){

        }
    });

    self.$textarea.val('').keyup().trigger('input.autosize');
};

OwComments.prototype.initTextarea = function()
{
    /* ODE */
      ODE.reset();
      ODE.addOdeOnComment();
    /* ODE */

    var self = this;
    this.realSubmitHandler = function(){

        self.initialCount++;

        //self.sendMessage(self.$textarea.val());
        ODE.commentSendMessage(self.$textarea.val(), self);

        self.attachmentInfo = false;
        self.oembedInfo = false;
        self.$hiddenBtnCont.hide();
        if( this.mediaAllowed ){
            OWLinkObserver.getObserver(self.textAreaId).resetObserver();
        }
        self.$attchCont.empty();
        OW.trigger('base.photo_attachment_reset', {pluginKey:self.pluginKey, uid:self.attchUid});
        OW.trigger('base.comment_add', self.eventParams);

        self.$formWrapper.addClass('ow_preloader');
        self.$commentsInputCont.hide();

    };

    this.submitHandler = this.realSubmitHandler;

    this.$textarea
        .bind('keypress',
        function(e){
            if( e.which === 13 && !e.shiftKey ){
                e.stopImmediatePropagation();
                var textBody = $(this).val();

                if ( $.trim(textBody) == '' && !self.attachmentInfo && !self.oembedInfo ){
                    OW.error(self.labels.emptyCommentMsg);
                    return false;
                }

                self.submitHandler();
                return false;
            }
        }
    )
        .one('focus', function(){$(this).removeClass('invitation').val('').autosize({callback:function(data){OW.trigger('base.comment_textarea_resize', self.eventParams);}});});

    this.$hiddenBtnCont.unbind('click').click(function(){self.submitHandler();});

    if( this.mediaAllowed ){
        OWLinkObserver.observeInput(this.textAreaId, function( link ){
            if( !self.attachmentInfo ){
                self.$attchCont.html('<div class="ow_preloader" style="height: 30px;"></div>');
                this.requestResult( function( r ){
                    self.$attchCont.html(r);
                    self.$hiddenBtnCont.show();

                    OW.trigger('base.comment_attach_media', {})
                });
                this.onResult = function( r ){
                    self.oembedInfo = r;
                    if( $.isEmptyObject(r) ){
                        self.$hiddenBtnCont.hide();
                    }
                };
            }
        });
    }
};

ODE.reset = function()
{
    $('#ode_controllet_placeholder').hide();
    $('input[name=ode_datalet]').val("");
    $('input[name=ode_fields]').val("");
    $('input[name=ode_params]').val("");

    ODE.dataletParameters.component = "";
    ODE.dataletParameters.params    = "";
    ODE.dataletParameters.fields    = "";
    ODE.dataletParameters.data      = "";
    ODE.dataletParameters.comment   = "";

};