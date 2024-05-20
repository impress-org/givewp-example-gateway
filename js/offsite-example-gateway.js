/**
 * Start with a Self-Executing Anonymous Function (IIFE) to avoid polluting and conflicting with the global namespace (encapsulation).
 * @see https://developer.mozilla.org/en-US/docs/Glossary/IIFE
 *
 * This won't be necessary if you're using a build system like webpack.
 */
(() => {
  let settings = {};
  /**
   * Example of rendering gateway fields (without jsx).
   *
   * This renders a simple div with a label and input.
   *
   * @see https://react.dev/reference/react/createElement
   */
  function OffsiteExampleGatewayFields() {
    return window.wp.element.createElement(
      "div",
      {
        className: 'example-offsite-help-text'
      },
      window.wp.element.createElement(
        "p",
        {
          style: {marginBottom: 0}
        },
        settings.message,
      )
    );
  }

  /**
   * Example of a front-end gateway object.
   */
  const OffsiteExampleGateway = {
    id: "example-test-gateway-offsite",
    initialize() {
      settings = this.settings
    },
    Fields() {
      return window.wp.element.createElement(OffsiteExampleGatewayFields);
    },
  };

  /**
   * The final step is to register the front-end gateway with GiveWP.
   */
  window.givewp.gateways.register(OffsiteExampleGateway);
})();
