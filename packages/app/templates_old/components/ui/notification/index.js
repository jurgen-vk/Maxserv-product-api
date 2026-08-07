import {notify} from "@app/notifiers";

const mercureUrl = document.querySelector("meta[name=\"mercure-url\"]").content;

const source = new EventSource(
  mercureUrl + "?topic=" + encodeURIComponent("notifications")
);

source.onmessage = (event) => {
  const data = JSON.parse(event.data);
  notify(data.message, data.type, data.duration ?? undefined);
};

const notificationsMeta = document.querySelector("meta[name=\"notifications\"]");
const pending = notificationsMeta ? JSON.parse(notificationsMeta.content) : [];

pending.forEach((entry) => notify(entry.message, entry.type, entry.duration ?? undefined));
