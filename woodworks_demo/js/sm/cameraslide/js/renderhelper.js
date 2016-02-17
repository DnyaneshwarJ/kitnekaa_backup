/**
 * @package SM Camera Slideshow
 * @version 2.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright Copyright (c) 2014 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.magentech.com
 */
    //console.log('ghisdhi'); // hàm debug kiểm tra js
_MediabrowserUtility = {
    openDialog: function(a, b, c, d, e, f) {
        b = b || "browser_window";
        if ($(b) && "undefined" != typeof Windows) {
            Windows.focus(b);
            return;
        }
        this.dialogWindow = Dialog.info(null, Object.extend({
            closable: true,
            resizable: false,
            draggable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: e || "CameraSlide Insert File...",
            top: 50,
            width: c || 950,
            height: d || 600,
            zIndex: f && f.zIndex || 1e3,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: b,
            onClose: this.closeDialog.bind(this)
        }, f || {}));
        new Ajax.Updater("modal_dialog_message", a + "?popupId=" + b, {
            evalScripts: true
        });
    },
    closeDialog: function(a) {
        if (!a) a = this.dialogWindow;
        if (a) {
            WindowUtilities._showSelect();
            a.close();
        }
    }
};

Mediabrowser = Class.create();

Mediabrowser.prototype = {
    popupId: null,
    targetElementId: null,
    onInsertCallback: null,
    onInsertCallbackParams: null,
    contentsUrl: null,
    onInsertUrl: null,
    newFolderUrl: null,
    deleteFolderUrl: null,
    deleteFilesUrl: null,
    headerText: null,
    tree: null,
    currentNode: null,
    storeId: null,
    initialize: function(a) {
        this.popupId = a.popupId || "browser_window";
        this.newFolderPrompt = a.newFolderPrompt;
        this.deleteFolderConfirmationMessage = a.deleteFolderConfirmationMessage;
        this.deleteFileConfirmationMessage = a.deleteFileConfirmationMessage;
        this.targetElementId = a.targetElementId;
        this.onInsertCallback = a.onInsertCallback;
        this.onInsertCallbackParams = a.onInsertCallbackParams;
        this.contentsUrl = a.contentsUrl;
        this.onInsertUrl = a.onInsertUrl;
        this.newFolderUrl = a.newFolderUrl;
        this.deleteFolderUrl = a.deleteFolderUrl;
        this.deleteFilesUrl = a.deleteFilesUrl;
        this.headerText = a.headerText;
    },

    setTree: function(a) {
        this.tree = a;
        this.currentNode = a.getRootNode();
    },
    getTree: function(a) {
        return this.tree;
    },
    selectFolder: function(a, b) {
        this.currentNode = a;
        this.hideFileButtons();
        this.activateBlock("contents");
        if ("root" == a.id) this.hideElement("button_delete_folder"); else this.showElement("button_delete_folder");
        this.updateHeader(this.currentNode);
        this.drawBreadcrumbs(this.currentNode);
        this.showElement("loading-mask");
        new Ajax.Request(this.contentsUrl, {
            parameters: {
                node: this.currentNode.id
            },
            evalJS: true,
            onSuccess: function(a) {
                try {
                    this.currentNode.select();
                    this.onAjaxSuccess(a);
                    this.hideElement("loading-mask");
                    if (void 0 != $("contents")) {
                        $("contents").update(a.responseText);
                        $$("div.filecnt").each(function(a) {
                            Event.observe(a.id, "click", this.selectFile.bind(this));
                            Event.observe(a.id, "dblclick", this.insert.bind(this));
                        }.bind(this));
                    }
                } catch (b) {
                    alert(b.message);
                }
            }.bind(this)
        });
    },
    selectFolderById: function(a) {
        var b = this.tree.getNodeById(a);
        if (b.id) this.selectFolder(b);
    },
    selectFile: function(a) {
        var b = Event.findElement(a, "DIV");
        $$('div.filecnt.selected[id!="' + b.id + '"]').each(function(a) {
            a.removeClassName("selected");
        });
        b.toggleClassName("selected");
        if (b.hasClassName("selected")) this.showFileButtons(); else this.hideFileButtons();
    },
    showFileButtons: function() {
        this.showElement("button_delete_files");
        this.showElement("button_insert_files");
    },
    hideFileButtons: function() {
        this.hideElement("button_delete_files");
        this.hideElement("button_insert_files");
    },
    handleUploadComplete: function(a) {
        $$('div[class*="file-row complete"]').each(function(a) {
            $(a.id).remove();
        });
        this.selectFolder(this.currentNode);
    },
    insert: function(a) {
        var b;
        if (void 0 != a) b = Event.findElement(a, "DIV"); else $$("div.filecnt.selected").each(function(a) {
            b = $(a.id);
        });
        if (void 0 == $(b.id)) return false;
        var c = this.getTargetElement();
        if (!c && !this.onInsertCallback) {
            alert("Target element not found for content update");
            Windows.close(this.popupId);
            return;
        }
        var d = {
            filename: b.id,
            node: this.currentNode.id,
            store: this.storeId
        };
        if (c && "textarea" == c.tagName.toLowerCase()) d.as_is = 1;
        new Ajax.Request(this.onInsertUrl, {
            parameters: d,
            onSuccess: function(a) {
                try {
                    this.onAjaxSuccess(a);
                    if (this.getMediaBrowserOpener()) self.blur();
                    Windows.close(this.popupId);
                    if (c) if ("input" == c.tagName.toLowerCase()) {
                        c.value = a.responseText;
                        if (fireEvent) fireEvent(c, "change");
                    } else {
                        updateElementAtCursor(c, a.responseText);
                        if (varienGlobalEvents) varienGlobalEvents.fireEvent("tinymceChange");
                    }
                    if (this.onInsertCallback) {
                        var b = this.onInsertCallback.split(".");
                        if (1 == b.length) window[b[0]](a.responseText, this.onInsertCallbackParams); else if (2 == b.length) window[b[0]][b[1]](a.responseText, this.onInsertCallbackParams);
                    }
                } catch (d) {
                    alert(d.message);
                }
            }.bind(this)
        });
    }
    ,
    getTargetElement: function() {
        if ("undefined" != typeof tinyMCE && tinyMCE.get(this.targetElementId)) if (opener = this.getMediaBrowserOpener()) {
            var a = tinyMceEditors.get(this.targetElementId).getMediaBrowserTargetElementId();
            return opener.document.getElementById(a);
        } else return null; else return document.getElementById(this.targetElementId);
    },
    getMediaBrowserOpener: function() {
        if ("undefined" != typeof tinyMCE && tinyMCE.get(this.targetElementId) && "undefined" != typeof tinyMceEditors && !tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener().closed) return tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener(); else return null;
    },
    newFolder: function() {
        var a = prompt(this.newFolderPrompt);
        if (!a) return false;
        new Ajax.Request(this.newFolderUrl, {
            parameters: {
                name: a
            },
            onSuccess: function(a) {
                try {
                    this.onAjaxSuccess(a);
                    if (a.responseText.isJSON()) {
                        var b = a.responseText.evalJSON();
                        var c = new Ext.tree.AsyncTreeNode({
                            text: b.short_name,
                            draggable: false,
                            id: b.id,
                            expanded: true
                        });
                        var d = this.currentNode.appendChild(c);
                        this.tree.expandPath(d.getPath(), "", function(a, b) {
                            this.selectFolder(b);
                        }.bind(this));
                    }
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },
    deleteFolder: function() {
        if (!confirm(this.deleteFolderConfirmationMessage)) return false;
        new Ajax.Request(this.deleteFolderUrl, {
            onSuccess: function(a) {
                try {
                    this.onAjaxSuccess(a);
                    var b = this.currentNode.parentNode;
                    b.removeChild(this.currentNode);
                    this.selectFolder(b);
                } catch (c) {
                    alert(c.message);
                }
            }.bind(this)
        });
    },
    deleteFiles: function() {
        if (!confirm(this.deleteFileConfirmationMessage)) return false;
        var a = [];
        var b = 0;
        $$("div.selected").each(function(c) {
            a[b] = c.id;
            b++;
        });
        new Ajax.Request(this.deleteFilesUrl, {
            parameters: {
                files: Object.toJSON(a)
            },
            onSuccess: function(a) {
                try {
                    this.onAjaxSuccess(a);
                    this.selectFolder(this.currentNode);
                } catch (b) {
                    alert(b.message);
                }
            }.bind(this)
        });
    },
    drawBreadcrumbs: function(a) {
        if (void 0 != $("breadcrumbs")) $("breadcrumbs").remove();
        if ("root" == a.id) return;
        var b = a.getPath().split("/");
        var c = "";
        for (var d = 0, e = b.length; d < e; d++) {
            if ("" == b[d]) continue;
            var f = this.tree.getNodeById(b[d]);
            if (f.id) {
                c += "<li>";
                c += '<a href="#" onclick="MediabrowserInstance.selectFolderById(\'' + f.id + "');\">" + f.text + "</a>";
                if (d < e - 1) c += " <span>/</span>";
                c += "</li>";
            }
        }
        if ("" != c) {
            c = '<ul class="breadcrumbs" id="breadcrumbs">' + c + "</ul>";
            $("content_header").insert({
                after: c
            });
        }
    },
    updateHeader: function(a) {
        var b = "root" == a.id ? this.headerText : a.text;
        if (void 0 != $("content_header_text")) $("content_header_text").innerHTML = b;
    },
    activateBlock: function(a) {
        this.showElement(a);
    },
    hideElement: function(a) {
        if (void 0 != $(a)) {
            $(a).addClassName("no-display");
            $(a).hide();
        }
    },
    showElement: function(a) {
        if (void 0 != $(a)) {
            $(a).removeClassName("no-display");
            $(a).show();
        }
    },
    onAjaxSuccess: function(a) {
        if (a.responseText.isJSON()) {
            var b = a.responseText.evalJSON();
            if (b.error) throw b; else if (b.ajaxExpired && b.ajaxRedirect) setLocation(b.ajaxRedirect);
        }
    }
};

var CameraSlide = CameraSlide || {};

CameraSlide.InPlaceEditor = Class.create(Ajax.InPlaceEditor, {
    createControl: function(a, b, c) {
        var d = this.options[a + "Control"];
        var e = this.options[a + "Text"];
        if ("button" == d) {
            if ("ok" == a) var f = new Element("button", {
                type: "submit"
            }); else var f = new Element("button", {
                type: "button"
            });
            var g = new Element("span");
            g.update(e);
            f.update(g);
            if ("ok" == a) f.addClassName("save editor_" + a + "_button"); else if ("cancel" == a) f.addClassName("delete editor_" + a + "_button"); else f.addClassName("editor_" + a + "_button");
            f.observe("click", b);
            this._form.appendChild(f);
            this._controls[a] = f;
        } else if ("link" == d) {
            var h = document.createElement("a");
            h.href = "#";
            h.appendChild(document.createTextNode(e));
            h.onclick = "cancel" == a ? this._boundCancelHandler : this._boundSubmitHandler;
            h.className = "editor_" + a + "_link";
            if (c) h.className += " " + c;
            this._form.appendChild(h);
            this._controls[a] = h;
        }
    },
    createEditField: function() {
        var a = this.options.loadTextURL ? this.options.loadingText : this.getText();
        var b;
        if (1 >= this.options.rows && !/\r|\n/.test(this.getText())) {
            b = new Element("input");
            b.type = "text";
            var c = this.options.size || this.options.cols || 0;
            if (0 < c) b.size = c;
        } else {
            b = document.createElement("textarea");
            b.rows = 1 >= this.options.rows ? this.options.autoRows : this.options.rows;
            b.cols = this.options.cols || 40;
        }
        b.setStyle({
            width: "50px",
            verticalAlign: "top"
        });
        b.name = this.options.paramName;
        b.value = a;
        b.className = "editor_field input-text";
        b.setAttribute("maxlength", 6);
        if (this.options.submitOnBlur) b.onblur = this._boundSubmitHandler;
        this._controls.editor = b;
        if (this.options.loadTextURL) this.loadExternalText();
        this._form.appendChild(this._controls.editor);
    }
});

function bindInlineEdit(a) {
    var b = $(a).readAttribute("attr"), c = $(a).readAttribute("entity"), d = $(a).readAttribute("control"), e = $(a).readAttribute("saveUrl");
    switch (d) {
        case "text":
            new CameraSlide.InPlaceEditor(a, e, {
                callback: function(a, d) {
                    return "entity=" + c + "&attr=" + b + "&value=" + encodeURIComponent(d);
                },
                onComplete: function(b) {
                    if ("object" === typeof b) {
                        var c = b.responseText.evalJSON();
                        if (c.message) alert(c.message); else $(a).update(c.value);
                    }
                },
                onFailure: function() {
                    alert(Translator.translate("Error communicating with the server"));
                },
                cancelControl: "button",
                cancelText: "",
                okText: "",
                ajaxOptions: {
                    loaderArea: false
                }
            });
    }
}

//document.observe("dom:loaded", function() {
//    function a(a, b, c) {
//        if (c) {
//            b.down("a").addClassName("open");
//            b.removeClassName("collapsed").setStyle({
//                marginBottom: "0px"
//            });
//            a.show();
//        } else {
//            b.down("a").removeClassName("open");
//            b.addClassName("collapsed").setStyle({
//                marginBottom: "3px"
//            });
//            a.hide();
//        }
//    }
//    var b = new RegExp("collapse-group", "i"), c = 0, d = $$("div.collapsible.fieldset");
//    Event.observe(document, "collapse:open", function(c) {
//        d.each(function(d) {
//            if (d.collapseIndex != c.memo.index) if (b.test(d.className)) {
//                var e = d.previous();
//                if (e.hasClassName("entry-edit-head")) if (d.hasClassName("collapsed")) a(d, e, true); else a(d, e, false);
//            }
//        });
//    });
//    d.each(function(d) {
//        d.setStyle({
//            marginTop: "0px"
//        });
//        var e = d.previous();
//        if (e.hasClassName("entry-edit-head")) {
//            var f = b.test(d.className);
//            e.addClassName("collapseable");
//            e.setStyle({
//                cursor: "pointer"
//            });
//            var g = new Element("a", {
//                "class": "open"
//            });
//            g.setStyle({
//                width: "20px",
//                height: "16px"
//            });
//            e.down(".form-buttons").appendChild(g);
//            e.observe("click", function() {
//                if (this.hasClassName("collapsed")) {
//                    a(d, e, true);
//                    f && Element.fire(d, "collapse:open", {
//                        index: d.collapseIndex
//                    });
//                } else a(d, e, false);
//            });
//            if (f) {
//                d.collapseIndex = c++;
//                if (d.hasClassName("collapse-active")) a(d, e, true); else a(d, e, false);
//            }
//        }
//    });
//});