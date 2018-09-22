(function(global, doc) {

    const doChooseContent = (selectable, configName, title, onConfirm) => {
        const token = document.querySelector('meta[name="CSRF-Token"]').content;
        const siteaccess = document.querySelector('meta[name="SiteAccess"]').content;
        const languageCode = document.querySelector('meta[name="LanguageCode"]').content;
        const config = JSON.parse(document.querySelector(`[data-udw-config-name="${configName}"]`).dataset.udwConfig);

        const openUdwEvent = new CustomEvent('openUdw', {
            detail: Object.assign(
                {
                    confirmLabel: 'Select content',
                    title: title,
                    multiple: false,
                    startingLocationId: window.eZ.adminUiConfig.universalDiscoveryWidget.startingLocationId,
                    onConfirm: onConfirm,
                    restInfo: {token, siteaccess},
                    canSelectContent: selectable,
                    cotfAllowedLanguages: [languageCode]
                },
                config
            ),
        });

        doc.body.dispatchEvent(openUdwEvent);
    };

    class EzBtnEmbedWithUDW extends AlloyEditor.EzBtnEmbed {
        chooseContent() {
            const isSelectable = this.props.udwIsSelectableMethod ? this[this.props.udwIsSelectableMethod] : (item, callback) => callback(true);
            const udwConfigName = this.props.udwConfigName;
            const udwTitle = this.props.udwTitle;
            const onConfirm = this[this.props.udwContentDiscoveredMethod].bind(this);

            doChooseContent(isSelectable, udwConfigName, udwTitle, onConfirm);
        }
    }

    class EzBtnImage extends AlloyEditor.EzBtnImage {
        chooseContent() {
            const isSelectable = this.props.udwIsSelectableMethod ? this[this.props.udwIsSelectableMethod] : (item, callback) => callback(true);
            const udwConfigName = this.props.udwConfigName;
            const udwTitle = this.props.udwTitle;
            const onConfirm = this[this.props.udwContentDiscoveredMethod].bind(this);

            doChooseContent(isSelectable, udwConfigName, udwTitle, onConfirm);
        }
    }

    class EzBtnLinkEdit extends AlloyEditor.ButtonLinkEdit {
        selectContent() {
            const openUDW = () => {
                const isSelectable = this.props.udwIsSelectableMethod;
                const udwConfigName = 'richtext_embed';
                const udwTitle = 'Select content';
                const onConfirm = (items) => {
                    this.state.element.setAttribute(
                        'href', 'ezlocation://' + items[0].id
                    );
                    this.focusEditedLink();
                };

                doChooseContent(isSelectable, udwConfigName, udwTitle, onConfirm);
            };

            this.setState({
                discoveringContent: true,
            }, openUDW.bind(this));
        }
    }

    AlloyEditor.Buttons[EzBtnEmbedWithUDW.key] = AlloyEditor.EzBtnEmbed = EzBtnEmbedWithUDW;
    AlloyEditor.Buttons[EzBtnImage.key] = AlloyEditor.EzBtnImage = EzBtnImage;
    AlloyEditor.Buttons[EzBtnLinkEdit.key] = AlloyEditor.ButtonLinkEdit = EzBtnLinkEdit;

    const customTags = Object.keys(global.eZ.adminUiConfig.richTextCustomTags);
    const customTagsToolbars = customTags.map(customTag => {
        const alloyEditorConfig = global.eZ.adminUiConfig.richTextCustomTags[customTag];

        return new global.eZ.ezAlloyEditor.ezCustomTagConfig({
            name: customTag,
            alloyEditor: alloyEditorConfig,
        });
    });

    customTags.forEach(customTag => {
        const tagConfig = global.eZ.adminUiConfig.richTextCustomTags[customTag];
        const className = `ezBtn${customTag.charAt(0).toUpperCase() + customTag.slice(1)}`;
        const editClassName = `${className}Edit`;
        const updateClassName = `${className}Update`;

        class buttonCustomTag extends global.eZ.ezAlloyEditor.ezBtnCustomTag {
            constructor(props) {
                super(props);

                const values = {};

                Object.keys(tagConfig.attributes).forEach(attr => {
                    values[attr] = {
                        value: tagConfig.attributes[attr].defaultValue
                    };
                });

                this.label = tagConfig.label;
                this.icon = tagConfig.icon || '/bundles/ezplatformadminui/img/ez-icons.svg#tag';
                this.customTagName = customTag;
                this.values = values;
            }

            static get key() {
                return customTag;
            }
        }

        class buttonCustomTagEdit extends global.eZ.ezAlloyEditor.ezBtnCustomTagEdit {
            constructor(props) {
                super(props);

                this.customTagName = customTag;
                this.attributes = tagConfig.attributes;
            }

            static get key() {
                return `${customTag}edit`;
            }
        }

        class buttonCustomTagUpdate extends global.eZ.ezAlloyEditor.ezBtnCustomTagUpdate {
            constructor(props) {
                super(props);

                this.customTagName = customTag;
                this.attributes = tagConfig.attributes;
            }

            static get key() {
                return `${customTag}update`;
            }
        }

        global.AlloyEditor.Buttons[buttonCustomTag.key] = global.AlloyEditor[className] = buttonCustomTag;
        global.AlloyEditor.Buttons[buttonCustomTagEdit.key] = global.AlloyEditor[editClassName] = buttonCustomTagEdit;
        global.AlloyEditor.Buttons[buttonCustomTagUpdate.key] = global.AlloyEditor[updateClassName] = buttonCustomTagUpdate;
    });

    const customStylesConfigurations = Object.entries(global.eZ.adminUiConfig.richTextCustomStyles).map(
        ([customStyleName, customStyleConfig]) => {
            return {
                name: customStyleConfig.label,
                style: {
                    element: customStyleConfig.inline ? 'span' : 'div',
                    attributes: {
                        'data-ezstyle': customStyleName,
                    },
                },
            };
        }
    );

    const container = global.document.querySelector('#block_configuration_attributes_content__editable');
    const alloyEditor = global.AlloyEditor.editable(container.getAttribute('id'), {
        toolbars: {
            ezadd: {
                buttons: [
                    'ezheading',
                    'ezparagraph',
                    'ezunorderedlist',
                    'ezorderedlist',
                    'ezimage',
                    'ezembed',
                    'eztable',
                ],
                tabIndex: 2,
            },
            styles: {
                selections: [
                    new window.eZ.ezAlloyEditor.ezLinkConfig(),
                    new window.eZ.ezAlloyEditor.ezTextConfig({ customStyles: customStylesConfigurations }),
                    new window.eZ.ezAlloyEditor.ezParagraphConfig({ customStyles: customStylesConfigurations }),
                    new window.eZ.ezAlloyEditor.ezCustomStyleConfig({ customStyles: customStylesConfigurations }),
                    new window.eZ.ezAlloyEditor.ezHeadingConfig({ customStyles: customStylesConfigurations }),
                    new window.eZ.ezAlloyEditor.ezTableConfig(),
                    new window.eZ.ezAlloyEditor.ezEmbedImageConfig(),
                    new window.eZ.ezAlloyEditor.ezEmbedConfig(),
                ],
                tabIndex: 1
            },
        },
        extraPlugins: AlloyEditor.Core.ATTRS.extraPlugins.value + ',' + [
            'ezaddcontent',
            'ezmoveelement',
            'ezremoveblock',
            'ezembed',
            'ezfocusblock',
            'ezcustomtag',
        ].join(','),
    });

    const getHTMLDocumentFragment = function (data) {
        const fragment = document.createDocumentFragment();
        const root = fragment.ownerDocument.createElement('div');
        const doc = (new DOMParser()).parseFromString(data, 'text/xml');
        const importChildNodes = (parent, element, skipElement) => {
            let i;
            let newElement;

            if (skipElement) {
                newElement = parent;
            } else {
                if (element.nodeType === Node.ELEMENT_NODE) {
                    newElement = parent.ownerDocument.createElement(element.localName);
                    for (i = 0; i !== element.attributes.length; i++) {
                        importChildNodes(newElement, element.attributes[i], false);
                    }
                    parent.appendChild(newElement);
                } else if (element.nodeType === Node.TEXT_NODE) {
                    parent.appendChild(parent.ownerDocument.createTextNode(element.nodeValue));
                    return;
                } else if (element.nodeType === Node.ATTRIBUTE_NODE) {
                    parent.setAttribute(element.localName, element.value);
                    return;
                } else {
                    return;
                }
            }

            for (i = 0; i !== element.childNodes.length; i++) {
                importChildNodes(newElement, element.childNodes[i], false);
            }
        };

        if (!doc || !doc.documentElement || doc.querySelector('parsererror')) {
            console.warn('Unable to parse the content of the RichText field');

            return fragment;
        }

        fragment.appendChild(root);

        importChildNodes(root, doc.documentElement, true);
        return fragment;
    };

    const wrapper = getHTMLDocumentFragment(container.closest('.ez-data-source').querySelector('textarea').value);
    const section = wrapper.childNodes[0];

    if (section) {
        if (!section.hasChildNodes()) {
            section.appendChild(document.createElement('p'));
        }

        alloyEditor.get('nativeEditor').setData(section.innerHTML);
    }

    container.addEventListener('blur', event => {
        const data = alloyEditor.get('nativeEditor').getData();
        const doc = document.createDocumentFragment();
        const root = document.createElement('div');
        const ezNamespace = 'http://ez.no/namespaces/ezpublish5/xhtml5/edit';
        const xhtmlNamespace = 'http://www.w3.org/1999/xhtml';
        const emptyEmbed = function (embedNode) {
            let element = embedNode.firstChild;
            let next;
            let removeClass = function () {};

            while (element) {
                next = element.nextSibling;
                if (!element.getAttribute || !element.getAttribute('data-ezelement')) {
                    embedNode.removeChild(element);
                }
                element = next;
            }

            embedNode.classList.forEach(function (cl) {
                let prevRemoveClass = removeClass;

                if (cl.indexOf('is-embed-') === 0) {
                    removeClass = function () {
                        embedNode.classList.remove(cl);
                        prevRemoveClass();
                    };
                }
            });
            removeClass();
        };
        const xhtmlify = function (data) {
            const doc = document.implementation.createDocument(xhtmlNamespace, 'html', null);
            const htmlDoc =  document.implementation.createHTMLDocument("");
            const section = htmlDoc.createElement('section');
            let body = htmlDoc.createElement('body');

            section.innerHTML = data;
            body.appendChild(section);
            body = doc.importNode(body, true);
            doc.documentElement.appendChild(body);

            return body.innerHTML;
        };

        root.innerHTML = data;
        doc.appendChild(root);

        [...doc.querySelectorAll('[data-ezelement="ezembed"]')].forEach(emptyEmbed);
        [...doc.querySelectorAll('[data-ezelement="ezcustomtag"]')].forEach(emptyEmbed);

        event.target.closest('.ez-data-source').querySelector('textarea').value = xhtmlify(root.innerHTML).replace(xhtmlNamespace, ezNamespace);
    });
}) (window, document);
