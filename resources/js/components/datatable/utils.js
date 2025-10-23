/**
 * Receives an object and return a possibly nested
 * attribute using dot notation. For example:
 *
 * Object: {
 *  some: {
 *      other: {
 *          data: 'Hello',
 *      },
 *  },
 * }
 *
 * If we use 'some.other.data' as a param argument in
 * the last object, it will return 'Hello'.
 *
 * @param {Obj} object
 * @param {String} param
 * @returns self|mixed
 */
function getNestedParamFromObject(object, param) {
    if (!param || !object) {
        return;
    }

    const isNested = (param) => param.includes(".");
    const before = (string, separator) =>
        string.substring(0, string.indexOf(separator));
    const after = (string, separator) =>
        string.slice(string.indexOf(separator) + 1);

    if (!isNested(param) && object.hasOwnProperty(param)) return object[param];

    const currentParam = before(param, ".");
    const nextParam = after(param, ".");

    if (!object.hasOwnProperty(currentParam)) return null;

    return getNestedParamFromObject(object[currentParam], nextParam);
}

function getQueryStringParam(param) {
    const querystring = new URLSearchParams(window.location.search);

    if (!querystring.has(param)) return null;

    return querystring.get(param);
}

export { getNestedParamFromObject, getQueryStringParam };
