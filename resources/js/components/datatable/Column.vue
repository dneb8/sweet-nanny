<script>
export default {
    name: "Column",
    props: {
        header: String,
        data: Object,
        onEmptyText: String,
        field: String,
        avatar: {
            type: Boolean,
            default: false,
        },
        collapseAlways: {
            type: Boolean,
            default: false,
        },
        headerClass: {
            type: String,
            default: "",
        },
        sortable: {
            type: Boolean,
            default: false,
        },
        clickable: {
            type: Function,
            default: null,
        },
        togglable: {
            type: Boolean,
            default: false,
        },
    },
    render() {
        return null;
    },
};
</script>
