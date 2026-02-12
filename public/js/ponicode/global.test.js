const rewire = require("rewire")
const _global = rewire("../global")
const scion = _global.__get__("scion")
// @ponicode
describe("scion.centralized_button", () => {
    test("0", () => {
        document.body.insertAdjacentHTML("afterbegin", `<div id="wrapper0"><div>
        	<div id="nw"></div>
        	<div id="sv"></div>
        	<div id="dlt"></div>
        	<div id="prnt"></div>
        </div>
        </div>`)
        let result = scion.centralized_button(true, 100, 1, false)
        expect(result).toMatchSnapshot()
        expect(document.getElementById("wrapper0")).to.matchSnapshot()
        document.body.removeChild(document.getElementById("wrapper0"))
    })

    test("1", () => {
        document.body.insertAdjacentHTML("afterbegin", `<div id="wrapper1"><div>
        	<div id="nw"></div>
        	<div id="sv"></div>
        	<div id="dlt"></div>
        	<div id="prnt"></div>
        </div>
        </div>`)
        let result = scion.centralized_button(false, 0, 100, true)
        expect(result).toMatchSnapshot()
        expect(document.getElementById("wrapper1")).to.matchSnapshot()
        document.body.removeChild(document.getElementById("wrapper1"))
    })

    test("2", () => {
        document.body.insertAdjacentHTML("afterbegin", `<div id="wrapper2"><div>
        	<div id="nw"></div>
        	<div id="sv"></div>
        	<div id="dlt"></div>
        	<div id="prnt"></div>
        </div>
        </div>`)
        let result = scion.centralized_button(false, 0, 1, false)
        expect(result).toMatchSnapshot()
        expect(document.getElementById("wrapper2")).to.matchSnapshot()
        document.body.removeChild(document.getElementById("wrapper2"))
    })

    test("3", () => {
        document.body.insertAdjacentHTML("afterbegin", `<div id="wrapper3"><div>
        	<div id="nw"></div>
        	<div id="sv"></div>
        	<div id="dlt"></div>
        	<div id="prnt"></div>
        </div>
        </div>`)
        let result = scion.centralized_button(true, -5.48, 100, false)
        expect(result).toMatchSnapshot()
        expect(document.getElementById("wrapper3")).to.matchSnapshot()
        document.body.removeChild(document.getElementById("wrapper3"))
    })

    test("4", () => {
        document.body.insertAdjacentHTML("afterbegin", `<div id="wrapper4"><div>
        	<div id="nw"></div>
        	<div id="sv"></div>
        	<div id="dlt"></div>
        	<div id="prnt"></div>
        </div>
        </div>`)
        let result = scion.centralized_button(true, 100, -100, false)
        expect(result).toMatchSnapshot()
        expect(document.getElementById("wrapper4")).to.matchSnapshot()
        document.body.removeChild(document.getElementById("wrapper4"))
    })

    test("5", () => {
        document.body.insertAdjacentHTML("afterbegin", `<div id="wrapper5"><div>
        	<div id="nw"></div>
        	<div id="sv"></div>
        	<div id="dlt"></div>
        	<div id="prnt"></div>
        </div>
        </div>`)
        let result = scion.centralized_button(false, Infinity, Infinity, false)
        expect(result).toMatchSnapshot()
        expect(document.getElementById("wrapper5")).to.matchSnapshot()
        document.body.removeChild(document.getElementById("wrapper5"))
    })
})
