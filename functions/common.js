/**
 * (c) 2005-2022 by Martin Willisegger
 * (c) 2026 by Esteban Monge - Sempai Space
 * Project   : Gohan ZQL
 * Component : common JavaScript functions
 * Website   : https://github.com/EstebanMonge/gohanzql
 * Version   : 4.0.0
 * GIT Repo  : https://github.com/EstebanMonge/gohanzql
 */
let popup = false;

function info(key1, key2, ver) {
    if (popup && popup.closed === false) popup.close();
    const top = (screen.availHeight - 240) / 2;
    const left = (screen.availWidth - 320) / 2;
    popup = window.open("info.php?key1=" + key1 + "&key2=" + key2 + "&version=" + ver,
        "Information",
        "width=320, height=240, top=" + top + ", left=" + left + ", SCROLLBARS=YES, MERNUBAR=NO, DEPENDENT=YES");
    popup.focus();
}

const myFocusObject = {};

function checkfields(fields, frm, object) {
    const ar_field = fields.split(",");
    for (let i = 0; i < ar_field.length; i++) {
        if (frm[ar_field[i]].value === "") {
            //frm[ar_field[i]].focus();
            object.myValue = frm[ar_field[i]];
            return false;
        }
    }
    return true;
}

function checkfields2(fields, frm, object) {
    const ar_field = fields.split(",");
    for (let i = 0; i < ar_field.length; i++) {
        if ((frm[ar_field[i]].value === "") || (frm[ar_field[i]].value === "0")) {
            //frm[ar_field[i]].focus();
            object.myValue = frm[ar_field[i]];
            return false;
        }
    }
    return true;
}

function checkboxes(fields, frm) {
    let retval = false;
    const ar_field = fields.split(",");
    for (let i = 0; i < ar_field.length; i++) {
        if (frm[ar_field[i]].checked === true) {
            retval = true;
        }
    }
    return retval;
}

// YUI 3 (@canonical/yui, bundled in functions/yui). The modules are loaded on demand, see YUI_config in main.htm.twig.
// Preload them, so they are already cached when the first dialog is opened.
YUI().use('panel', 'dd-plugin', 'dd-constrain', 'io-base', 'node', 'calendar', 'tabview', function (Y, status) {
    if (!status.success) {
        // Show the tabbed forms anyway, even if they can't be enhanced
        document.documentElement.className += ' nql-yui-failed';
    }
});

// Responsive menu: on small screens the menu is hidden, the button in the header shows and hides it
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('nql-menu-toggle');
    if (button && document.getElementById('nql-menu')) {
        document.body.classList.add('nql-has-menu');
        button.addEventListener('click', function () {
            const open = document.body.classList.toggle('nql-menu-open');
            const label = open ? button.getAttribute('data-hide') : button.getAttribute('data-show');
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            button.setAttribute('title', label);
            button.setAttribute('aria-label', label);
        });
    }
});

// Dialogs which are created once and reused
const nqlDialogs = {};

// Destroy a dialog when it is closed (used by message and confirm boxes)
function nqlDestroyOnHide(e) {
    if (!e.newVal) {
        this.destroy(true);
    }
}

// Body of message and confirm boxes: optional icon (1 = warning, 2 = question) and text
function nqlDialogBody(msg, type) {
    let icon = '';
    if (type === 1) {
        icon = '<div class="nql-dialog-icon nql-dialog-icon-warn"></div>';
    } else if (type === 2) {
        icon = '<div class="nql-dialog-icon nql-dialog-icon-help"></div>';
    }
    return '<div class="nql-dialog-body">' + icon + '<div class="nql-dialog-text">' + msg + '</div></div>';
}

// Make a dialog draggable by its header, but keep it inside of the viewport
function nqlMakeDraggable(Y, panel) {
    panel.headerNode.setStyle('cursor', 'move');
    panel.get('boundingBox').plug(Y.Plugin.Drag, {handles: ['.yui3-widget-hd']}).dd.plug(Y.Plugin.DDConstrained, {
        constrain2view: true
    });
}

// Load a page into an element
function nqlLoadContent(Y, url, elementId) {
    Y.io(url, {
        on: {
            success: function (id, o) {
                if (o.responseText !== undefined) {
                    document.getElementById(elementId).innerHTML = o.responseText;
                }
            },
            failure: function (id, o) {
                if (o.responseText !== undefined) {
                    document.getElementById(elementId).innerHTML = "No information found";
                }
            }
        }
    });
}

// YUI message box
function msginit(msg, header, type) {
    YUI().use('panel', function (Y) {
        const panel = new Y.Panel({
            headerContent: header,
            bodyContent: nqlDialogBody(msg, type),
            width: '300px',
            zIndex: 1000,
            centered: true,
            constrain: true,
            modal: true,
            visible: false,
            render: true,
            buttons: {
                header: ['close'],
                footer: [{label: 'Ok', isDefault: true, action: 'hide'}]
            }
        });
        panel.after('visibleChange', nqlDestroyOnHide);
        panel.show();
    });
}

// YUI confirm box
function confirminit(msg, header, type, yes, no, key) {
    YUI().use('panel', function (Y) {
        const panel = new Y.Panel({
            headerContent: header,
            bodyContent: nqlDialogBody(msg, type === 1 ? 1 : 0),
            width: '400px',
            zIndex: 1000,
            centered: true,
            constrain: true,
            modal: true,
            visible: false,
            render: true,
            buttons: {
                header: ['close'],
                footer: [
                    {
                        label: yes, isDefault: true, action: function () {
                            // noinspection JSUnresolvedFunction
                            confOpenerYes(key);
                            this.hide();
                        }
                    },
                    {label: no, action: 'hide'}
                ]
            }
        });
        panel.after('visibleChange', nqlDestroyOnHide);
        panel.show();
    });
}

// YUI info dialog
function dialoginit(key1, key2, ver, header) {
    YUI().use('panel', 'dd-plugin', 'dd-constrain', 'io-base', 'node', function (Y) {
        let sUrl;
        if (key2 === "updInfo") {
            sUrl = "admin/info.php?key1=" + key1 + "&key2=" + key2 + "&version=" + ver;
        } else {
            sUrl = "info.php?key1=" + key1 + "&key2=" + key2 + "&version=" + ver;
        }
        nqlLoadContent(Y, sUrl, 'dialogcontent');

        if (typeof nqlDialogs.info === "undefined") {
            nqlDialogs.info = new Y.Panel({
                bodyContent: Y.one('#dialogcontent'),
                width: '50em',
                zIndex: 1000,
                centered: true,
                constrain: true,
                visible: false,
                render: true,
                buttons: {
                    header: ['close'],
                    footer: [{label: 'Ok', isDefault: true, action: 'hide'}]
                }
            });
            nqlMakeDraggable(Y, nqlDialogs.info);
        }
        nqlDialogs.info.set('headerContent', header);
        nqlDialogs.info.show();
    });
}

// YUI calendar
function calendarinit(lang, start, field, key, cont, obj) {
    YUI({lang: (lang === "de_DE") ? "de" : "en"}).use('panel', 'dd-plugin', 'dd-constrain', 'calendar', 'node', function (Y) {
        Y.on('domready', function () {
            // The container holds the title and the (empty) element for the calendar
            const panel = new Y.Panel({
                headerContent: Y.one('#' + cont + ' .hd').getHTML(),
                bodyContent: Y.one('#' + obj),
                zIndex: 1000,
                align: {node: '#' + field, points: [Y.WidgetPositionAlign.TL, Y.WidgetPositionAlign.BL]},
                visible: false,
                render: true,
                buttons: {header: ['close']}
            });
            nqlMakeDraggable(Y, panel);

            const calendar = new Y.Calendar({
                strings: Y.merge(Y.Intl.get('calendar-base'), {first_weekday: start})
            });
            calendar.render('#' + obj);
            calendar.on('dateClick', function (e) {
                let month = e.date.getMonth() + 1, day = e.date.getDate();
                if (month < 10) {
                    month = "0" + month;
                }
                if (day < 10) {
                    day = "0" + day;
                }
                document.getElementById(field).value = e.date.getFullYear() + "-" + month + "-" + day;
                panel.hide();
            });

            Y.one('#' + key).on('click', function () {
                panel.show();
                // on small screens the field can be at the bottom of the page
                panel.get('boundingBox').getDOMNode().scrollIntoView({block: 'nearest'});
            });
        });
    });
}

// Tabbed form
function nqlTabView(id) {
    YUI().use('tabview', 'node', function (Y) {
        new Y.TabView({srcNode: '#' + id}).render();
        Y.one('#' + id).addClass('nql-tabview-ready');
    });
}

// Open edit dialog for list boxes
function openMutDlgInit(field, divbox, header, key, langkey1, langkey2, exclude) {
    YUI().use('panel', 'dd-plugin', 'dd-constrain', 'io-base', 'node', function (Y) {
        Y.on('domready', function () {
            nqlLoadContent(Y, "mutdialog.php?object=" + field + "&exclude=" + exclude, divbox + 'content');

            const handleSave = function () {
                const source = document.getElementById(field);
                const targetSelect = document.getElementById(field + 'Selected');
                //const targetAvail = document.getElementById(field + 'Avail');
                for (let i = 0; i < targetSelect.length; ++i) {
                    targetSelect.options[i].selected = true;
                }
                for (let i = 0; i < source.length; ++i) {
                    source.options[i].selected = false;
                    source.options[i].className = source.options[i].className.replace(/ ieselected/g, '');
                }
                for (let i = 0; i < targetSelect.length; ++i) {
                    for (let y = 0; y < source.length; ++y) {
                        const value1 = targetSelect.options[i].value.replace(/^e/g, '');
                        const value2 = "e" + value1;
                        if ((source.options[y].value === value1) || (source.options[y].value === value2)) {
                            source.options[y].selected = true;
                            source.options[y].value = targetSelect.options[i].value;
                            source.options[y].text = targetSelect.options[i].text;
                            source.options[y].className = source.options[y].className + " ieselected";
                        }
                    }
                }
                this.hide();
                // noinspection JSUnresolvedVariable
                if ((typeof (update) === 'number') && (update === 1)) {
                    // noinspection JSUnresolvedFunction
                    updateForm(field);
                }
            };
            const mutdialog = new Y.Panel({
                headerContent: header,
                bodyContent: Y.one('#' + divbox + 'content'),
                width: '60em',
                zIndex: 1000,
                centered: true,
                constrain: true,
                modal: true,
                visible: false,
                render: true,
                buttons: {
                    header: ['close'],
                    footer: [{label: langkey1, isDefault: true, action: handleSave}, {label: langkey2, action: 'hide'}]
                }
            });
            nqlMakeDraggable(Y, mutdialog);
            mutdialog.before('visibleChange', function (e) {
                if (e.newVal) {
                    getData(field);
                }
            });

            Y.one('#' + key).on('click', function () {
                mutdialog.show();
            });
        });
    });
}

// Additional functions for edit dialog
function getData(field) {
    const source = document.getElementById(field);
    const targetSelect = document.getElementById(field + 'Selected');
    const targetAvail = document.getElementById(field + 'Avail');
    for (let i = 0; i < targetSelect.length; i++) {
        targetSelect.options[i] = null;
    }
    // noinspection JSUndefinedPropertyAssignment
    targetSelect.length = 0;
    for (let i = 0; i < targetAvail.length; i++) {
        targetAvail.options[i] = null;
    }
    // noinspection JSUndefinedPropertyAssignment
    targetAvail.length = 0;
    let NeuerEintrag1;
    let NeuerEintrag2;
    for (let i = 0; i < source.length; ++i) {
        if (source.options[i].selected === true) {
            NeuerEintrag1 = new Option(source.options[i].text, source.options[i].value, false, false);
            NeuerEintrag1.className = source.options[i].className.replace(/ ieselected/g, '');
            NeuerEintrag1.className = NeuerEintrag1.className.replace(/ inpmust/g, '');
            targetSelect.options[targetSelect.length] = NeuerEintrag1;
        }
        if (source.options[i].selected === false) {
            if (source.options[i].text !== "") {
                NeuerEintrag2 = new Option(source.options[i].text, source.options[i].value, false, false);
                NeuerEintrag2.className = source.options[i].className.replace(/ ieselected/g, '');
                NeuerEintrag2.className = NeuerEintrag2.className.replace(/ inpmust/g, '');
                targetAvail.options[targetAvail.length] = NeuerEintrag2;
            }
        }
    }
}

// Insert selection
function selValue(field) {
    const targetSelect = document.getElementById(field + 'Selected');
    const targetAvail = document.getElementById(field + 'Avail');
    let NeuerEintrag;
    if (targetAvail.selectedIndex !== -1) {
        const DelOptions = [];
        for (let i = 0; i < targetAvail.length; ++i) {
            if (targetAvail.options[i].selected === true) {
                NeuerEintrag = new Option(targetAvail.options[i].text, targetAvail.options[i].value, false, false);
                NeuerEintrag.className = targetAvail.options[i].className;
                targetSelect.options[targetSelect.length] = NeuerEintrag;
                DelOptions.push(i);
            }
        }
        sort(targetSelect);
        DelOptions.reverse();
        for (let i = 0; i < DelOptions.length; ++i) {
            targetAvail.options[DelOptions[i]] = null;
        }
    }
}

// Insert selection (exclude variant)
function selValueEx(field) {
    const targetSelect = document.getElementById(field + 'Selected');
    const targetAvail = document.getElementById(field + 'Avail');
    let NeuerEintrag;
    if (targetAvail.selectedIndex !== -1) {
        const DelOptions = [];
        for (let i = 0; i < targetAvail.length; ++i) {
            if (targetAvail.options[i].selected === true) {
                if ((targetAvail.options[i].text !== '*') && (targetAvail.options[i].value !== '0')) {
                    NeuerEintrag = new Option("!" + targetAvail.options[i].text, "e" + targetAvail.options[i].value, false, false);
                } else {
                    NeuerEintrag = new Option(targetAvail.options[i].text, targetAvail.options[i].value, false, false);
                }
                NeuerEintrag.className = targetAvail.options[i].className;
                targetSelect.options[targetSelect.length] = NeuerEintrag;
                DelOptions.push(i);
            }
        }
        sort(targetSelect);
        DelOptions.reverse();
        for (let i = 0; i < DelOptions.length; ++i) {
            targetAvail.options[DelOptions[i]] = null;
        }
    }
}

// Remove selection
function desValue(field) {
    const targetSelect = document.getElementById(field + 'Selected');
    const targetAvail = document.getElementById(field + 'Avail');
    let NeuerEintrag;
    if (targetSelect.selectedIndex !== -1) {
        const DelOptions = [];
        for (let i = 0; i < targetSelect.length; ++i) {
            if (targetSelect.options[i].selected === true) {
                const text = targetSelect.options[i].text.replace(/^!/g, '');
                const value = targetSelect.options[i].value.replace(/^e/g, '');
                NeuerEintrag = new Option(text, value, false, false);
                NeuerEintrag.className = targetSelect.options[i].className;
                targetAvail.options[targetAvail.length] = NeuerEintrag;
                DelOptions.push(i);
            }
        }
        sort(targetAvail);
        DelOptions.reverse();
        for (let i = 0; i < DelOptions.length; ++i) {
            targetSelect.options[DelOptions[i]] = null;
        }
    }
}

// Sort entries
function sort(obj) {
    const sortieren = [];
    const list = [];
    let i;

    // Insert list to array
    for (i = 0; i < obj.options.length; i++) {
        list[i] = [];
        list[i]["text"] = obj.options[i].text;
        list[i]["value"] = obj.options[i].value;
        list[i]["className"] = obj.options[i].className;
    }

    // Sort into a single dimension array
    for (i = 0; i < obj.length; i++) {
        sortieren[i] = list[i]["text"] + ";" + list[i]["value"] + ";" + list[i]["className"];
    }

    // Real sort
    sortieren.sort();

    // Make array to list
    for (i = 0; i < sortieren.length; i++) {
        const felder = sortieren[i].split(";");
        list[i]["text"] = felder[0];
        list[i]["value"] = felder[1];
        list[i]["className"] = felder[2];
    }

    // Remove list field
    for (i = 0; i < obj.options.length; i++) {
        obj.options[i] = null;
    }

    // insert list to dialog
    let NeuerEintrag;
    for (i = 0; i < list.length; i++) {
        NeuerEintrag = new Option(list[i]["text"], list[i]["value"], false, false);
        NeuerEintrag.className = list[i]["className"];
        obj.options[i] = NeuerEintrag;
    }
}

// Show relation data
function showRelationData(option) {
    if (option === 1) {
        document.getElementById("rel_text").className = "elementHide";
        document.getElementById("rel_info").className = "elementShow";
    } else {
        document.getElementById("rel_text").className = "elementShow";
        document.getElementById("rel_info").className = "elementHide";
    }
}