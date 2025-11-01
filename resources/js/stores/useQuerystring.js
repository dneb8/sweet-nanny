import { defineStore } from "pinia";

export const useQuerystringStore = defineStore("querystring", {
    state: () => ({
        querystring: new URLSearchParams(window.location.search),
    }),
    getters: {
        __instance(state) {
            return state.querystring;
        },
    },
    actions: {
        get(attr) {
            return this.querystring.get(attr);
        },
        set(attr, value, defaultValue) {
            try {
                // It wont display the provided default value on the querystring
                if (defaultValue === value) {
                    this.querystring.delete(attr);
                } else {
                    this.querystring.set(attr, value);
                }

                this.refreshQuerystring();

                return true;
            } catch {
                return false;
            }
        },
        refreshQuerystring() {
            const path = window.location.pathname;
            const hash = window.location.hash;
            const querystring = this.querystring.toString();

            // Update URL
            window.history.replaceState(
                {},
                "",
                `${path}?${querystring}${hash}`
            );
        },
    },
});
