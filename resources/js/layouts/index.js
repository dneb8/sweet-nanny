import dom from "@left4code/tw-starter/dist/js/dom";

// Setup side menu
const findActiveMenu = (subMenu, route) => {
    let match = false;
    subMenu.forEach((item) => {
        if ( route.includes(item.route) ) {
            match = true;
        } else if (!match && item.subMenu) {
            match = findActiveMenu(item.subMenu, route);
        }
    });
    return match;
};

const nestedMenu = (menu, currentRoute) => {
    menu.forEach((item, key) => {
        if (typeof item !== "string") {
            let menuItem = menu[key];
            menuItem.active = (
                currentRoute.includes(item.route)
                || (item.subMenu && findActiveMenu(item.subMenu, currentRoute))
            );

            if (item.subMenu) {
                menuItem.activeDropdown = findActiveMenu(item.subMenu, currentRoute);
                menuItem = { ...item, ...nestedMenu(item.subMenu, currentRoute) };
            }
        }
    });

    return menu;
}

const enter = (el) => {
    dom(el).slideDown(300);
};

const leave = (el) => {
    dom(el).slideUp(300);
};

export { nestedMenu, enter, leave };