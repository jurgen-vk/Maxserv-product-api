export class ImportStartedEvent {
  constructor(importRecord) {
    this.import = importRecord;
  }
}