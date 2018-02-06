<?php
/**
 * Section template.
 *
 * @since 3.0.0
 */

learn_press_admin_view( 'course/section-item' );
learn_press_admin_view( 'course/new-section-item' );
?>

<script type="text/x-template" id="tmpl-lp-section">
    <div class="section" :class="[isOpen ? 'open' : 'close', status]" :data-section-order="index"
         :data-section-id="section.id">
        <div class="section-head" @dblclick="toggle">
            <span class="movable"></span>
            <!--Section title-->
            <input v-model="section.title" type="text" title="title" class="title-input"
                   @change="updating" @blur="completed" @keyup.enter="completed"
                   placeholder="<?php esc_attr_e( 'Enter the name section', 'learnpress' ); ?>">
            <!--Section toggle-->
            <div class="actions">
                <span class="collapse" :class="isOpen ? 'open' : 'close'" @click.prevent="toggle"></span>
            </div>
        </div>

        <div class="section-collapse" ref="collapse">
            <div class="section-content">
                <div class="details">
                    <!--Section description-->
                    <input v-model="section.description" type="text" class="description-input no-submit"
                           title="description"
                           @change="updating" @blur="completed" @keyup.enter="completed" ref="description"
                           placeholder="<?php echo esc_attr( 'Describe about this section', 'learnpress' ); ?>">
                </div>

                <div class="section-list-items" :class="{'no-item': !section.items.length}">
                    <draggable v-model="items" :element="'ul'" :options="optionDraggable">
                        <!--Section items-->
                        <lp-section-item v-for="(item, index) in section.items" :item="item" :key="item.id"
                                         @update="updateItem" @remove="removeItem" @delete="deleteItem" @nav="navItem"
                                         :order="index+1" :ref="index+1"
                                         :disableCurriculum="disableCurriculum"></lp-section-item>
                    </draggable>

                    <lp-new-section-item @create="newItem" v-if="!disableCurriculum"></lp-new-section-item>
                </div>
            </div>

            <div class="section-actions" v-if="!disableCurriculum">
                <button type="button" class="button button-secondary"
                        @click="openModal"><?php esc_html_e( 'Select items', 'learnpress' ); ?></button>

                <div class="remove" :class="{confirm: confirm}">
                    <span class="icon" @click="removing"><span class="dashicons dashicons-trash"></span></span>
                    <div class="confirm" @click="remove"><?php esc_html_e( 'Are you sure?', 'learnpress' ); ?></div>
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/javascript">
    (function (Vue, $store, $) {

        Vue.component('lp-section', {
            template: '#tmpl-lp-section',
            props: ['section', 'index', 'disableCurriculum'],
            data: function () {
                return {
                    changed: false,
                    confirm: false
                };
            },
            mounted: function () {
                var vm = this;

                this.prepareToggle();

                this.$watch('section.open', function (open) {
                    vm.toggleAnimation(open);
                });
            // },
            // created: function () {
            //     var _self = this;
            //     setTimeout(function () {
            //         var $el = jQuery('.section-list-items > ul');
            //         $el.sortable({
            //             handle: '.drag',
            //             axis: 'y',
            //             update: function () {
            //                 _self.sort();
            //             }
            //         });
            //     }, 1000)
            },
            computed: {
                status: function () {
                    return $store.getters['ss/statusUpdateSection'][this.section.id] || '';
                },
                isOpen: function () {
                    return this.section.open;
                },

                items: {
                    get: function () {
                        return this.section.items;
                    },
                    set: function (items) {
                        this.section.items = items;

                        $store.dispatch('ss/updateSectionItems', {
                            section_id: this.section.id,
                            items: items
                        });
                    }
                },

                optionDraggable: function () {
                    return {
                        handle: '.drag',
                        draggable: '.section-item',
                        group: {
                            name: 'lp-section-items',
                            put: true,
                            pull: true
                        }
                    };
                }
            },
            methods: {
                // toggle section
                toggle: function () {
                    $store.dispatch('ss/toggleSection', this.section);
                },
                prepareToggle: function () {
                    var display = 'none';
                    if (this.isOpen) {
                        display = 'block';
                    }

                    this.$refs.collapse.style.display = display;
                },
                toggleAnimation: function (open) {

                    if (open) {
                        $(this.$refs.collapse).slideDown();
                    } else {
                        $(this.$refs.collapse).slideUp();
                    }
                },
                // updating section
                updating: function () {
                    this.changed = true;
                },
                // update section
                completed: function () {
                    if (this.changed) {
                        $store.dispatch('ss/updateSection', this.section);
                        this.changed = false;
                    }
                },
                // click remove section
                removing: function () {
                    this.confirm = true;
                    var vm = this;

                    setTimeout(function () {
                        vm.confirm = false;
                    }, 3000);
                },
                // remove section
                remove: function () {
                    if (this.confirm) {
                        $store.dispatch('ss/removeSection', {index: this.index, section: this.section});
                        this.confirm = false;
                    }
                },
                // update section item
                updateItem: function (item) {
                    $store.dispatch('ss/updateSectionItem', {section_id: this.section.id, item: item});
                },
                // remove section item
                removeItem: function (item) {
                    $store.dispatch('ss/removeSectionItem', {section_id: this.section.id, item_id: item.id});
                },
                deleteItem: function (item) {
                    $store.dispatch('ss/deleteSectionItem', {section_id: this.section.id, item_id: item.id});
                },
                // navigation course items
                navItem: function (payload) {

                    var keyCode = payload.key,
                        order = payload.order;

                    if (keyCode === 38) {
                        if (order === 1) {
                            this.$refs.description.focus();
                        } else {
                            this.nav(order - 1);
                        }
                    }
                    if (keyCode === 40 || keyCode === 13) {
                        if (order === this.section.items.length) {
                            // code
                        } else {
                            this.nav(order + 1);
                        }
                    }

                },
                // focus item
                nav: function (position) {
                    var element = 'div[data-section-order=' + this.index + '] li[data-item-order=' + position + ']';
                    ($(element).find('.title input')).focus();
                },
                // new section item
                newItem: function (item) {
                    $store.dispatch('ss/newSectionItem', {section_id: this.section.id, item: item});
                },
                openModal: function () {
                    $store.dispatch('ci/open', parseInt(this.section.id));
                }
            }
        });

    })(Vue, LP_Curriculum_Store, jQuery);
</script>
