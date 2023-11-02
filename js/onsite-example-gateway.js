/**
 * Start with a Self-Executing Anonymous Function (IIFE) to avoid polluting and conflicting with the global namespace (encapsulation).
 *
 * This won't be necessary if you're using a build system like webpack.
 */
(() => {
  /**
   * Example of a gateway api.
   */
  const onsiteExampleGatewayApi = {
    clientKey: "",
    secureData: "",
    async submit() {
      if (!this.clientKey) {
        return {
          error: "OnsiteExampleGatewayApi clientKey is required.",
        };
      }
      if (this.secureData.length === 0) {
        return {
          error: "OnsiteExampleGatewayApi data is required.",
        };
      }
      return {
        transactionId: `oeg_transaction-${Date.now()}`,
      };
    },
  };

  /**
   * Example of rendering gateway fields (without jsx).
   *
   * This renders a simple div with a label and input.
   *
   * @see https://react.dev/reference/react/createElement
   */
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
          onChange(e) {
            onsiteExampleGatewayApi.secureData = e.target.value;
          },
        })
      )
    );
  }

  /**
   * Example of a front-end gateway object.
   */
  const OnsiteExampleGateway = {
    id: "onsite-example-test-gateway",
    initialize() {
      const { clientKey } = this.settings;

      onsiteExampleGatewayApi.clientKey = clientKey;
    },
    async beforeCreatePayment() {
      // Trigger form validation and wallet collection
      const { transactionId, error: submitError } =
        await onsiteExampleGatewayApi.submit();

      if (submitError) {
        throw new Error(submitError);
      }

      return {
        "example-gateway-id": transactionId,
      };
    },
    Fields() {
      return window.wp.element.createElement(OnsiteExampleGatewayFields);
    },
  };

  /**
   * The final step is to register the front-end gateway with GiveWP.
   */
  window.givewp.gateways.register(OnsiteExampleGateway);
})();
