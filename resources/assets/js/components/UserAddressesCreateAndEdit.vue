<template>
    <div>
        <input type="hidden" name="province" v-model="province">
        <input type="hidden" name="city" v-model="city">
        <input type="hidden" name="district" v-model="district">
        <select-district :init-value="initValue" @change="onDistrictChanged"></select-district>
    </div>
</template>

<script>
    import SelectDistrict from './SelectDistrictComponent.vue'
    export default {
        name: 'user-addresses-create-and-edit',
        // 组件的数据
        data() {
            return {
                province: '', // 省
                city: '', // 市
                district: '', // 区
            }
        },
        components: {
            SelectDistrict
        },
        props: {
            // 用来初始化省市区的值，在编辑时会用到
            initValue: {
                type: Array, // 格式是数组
                default: () => ([]), // 默认是个空数组
            }
        },
        methods: {
            // 把参数 val 中的值保存到组件的数据中
            onDistrictChanged(val) {
                if (val.length === 3) {
                    this.province = val[0];
                    this.city = val[1];
                    this.district = val[2];
                }
            }
        }
    }
</script>

<style>

</style>