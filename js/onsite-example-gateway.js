(() => {
  function OnsiteExampleGatewayFields() {
    return window.wp.element.createElement(
      "div",
      {},
      window.wp.element.createElement(
        "label",
        {
          htmlFor: "example-gateway-id",
          style: { display: "block", border: "none" },
        },
        "Onsite Example Test Gateway Label",
        window.wp.element.createElement("input", {
          className: "onsite-example-gateway",
          type: "text",
          name: "example-gateway-id",
        })
      )
    );
  }

  const OnsiteExampleGateway = {
    id: "onsite-example-test-gateway",
    async beforeCreatePayment() {
      const inputValue = document.forms[0].elements["example-gateway-id"].value;

      if (inputValue.length > 0) {
        const transactionId = `oeg_transaction-${Date.now()}`;

        return {
            'example-gateway-id': transactionId
        };
      }
    },
    Fields() {
      return window.wp.element.createElement(OnsiteExampleGatewayFields);
    },
  };

  window.givewp.gateways.register(OnsiteExampleGateway);
})();
