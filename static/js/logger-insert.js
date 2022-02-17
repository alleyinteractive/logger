
window.aiLogger = window.aiLogger || [];

class Logger {
  /**
   * Constructor.
   *
   * @param  {...any} args Array of arguments from `window.aiLogger`.
   */
  constructor(...args) {
    if (args.length) {
      args.forEach((item) => this.push(...item))
    }
  }

  /**
   * Handle a log event.
   *
   * @param {Array} record Log record.
   */
  async push(...args) {
    const [ message, logArgs = {} ] = args;
    const { nonce, url } = window.aiLoggerConfig;

    // Include a default trace with the arguments.
    if (logArgs && 'undefined' === typeof logArgs.trace) {
      logArgs.trace = new Error().stack;
    }

    // Include the current request information.
    logArgs.url = window.location.href;

    const body = new URLSearchParams();
    body.append('action', 'ai_logger_insert');
    body.append('ai_logger_nonce', nonce);
    body.append('args', JSON.stringify(logArgs));
    body.append('message', message);

    await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      body,
    });
  }
}

window.aiLogger = new Logger(...window.aiLogger || []);
