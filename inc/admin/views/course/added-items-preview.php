<?php
/**
 * Template added items preview.
 *
 * @since 3.0.0
 */

?>

<script type="text/x-template" id="tmpl-lp-added-items-preview">
    <div class="lp-added-items-preview" :class="{show:show}">
        <h3>List selected items ({{addedItems.length}})</h3>

        <ul class="list-added-items">
            <template v-for="(item, index) in addedItems">
                <li @click="removeItem(index)"><span v-html="item.title"></span></li>
            </template>
        </ul>
    </div>
</script>

<script>
    (function (Vue, $store) {
        Vue.component('lp-added-items-preview', {
            template: '#tmpl-lp-added-items-preview',
            props: {
                show: {
                    type: Boolean,
                    default: false
                }
            },
            methods: {
                removeItem: function (index) {
                    $store.dispatch('ci/removeItem', index);
                }
            },
            computed: {
                addedItems: function () {
                    return $store.getters['ci/addedItems'];
                }
            }
        });
    })(Vue, LP_Curriculum_Store);
</script>
