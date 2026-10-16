export default function receivingAssistant() {
    return {
        expected: 0,
        unit: "units",
        slots: [],
        actual: "",
        quantityVerified: false,
        planningMode: "quantity",
        perPallet: "",
        manualPalletCount: "",
        preferredLine: "",
        allocation: [],
        warehousePic: "",
        porter: "",
        confirmed: false,
        submitting: false,
        initialized: false,
        errorMessage: "",

        init() {
            const element = this.$el;

            try {
                this.expected = Number(element.dataset.expected || 0);

                this.unit = element.dataset.unit || "units";

                const rawSlots = element.dataset.slots || "[]";
                const parsedSlots = JSON.parse(rawSlots);

                if (!Array.isArray(parsedSlots)) {
                    throw new Error("Invalid warehouse location data.");
                }

                this.slots = parsedSlots.map((slot) => ({
                    id: slot.id,
                    line: String(slot.line),
                    slot: String(slot.slot),
                    occupied: Number(slot.occupied || 0),
                    summary: String(slot.summary || ""),
                }));

                if (
                    !Number.isSafeInteger(this.expected) ||
                    this.expected <= 0
                ) {
                    throw new Error("Invalid ordered quantity.");
                }

                this.initialized = true;
                this.errorMessage = "";
            } catch (error) {
                this.initialized = false;

                this.errorMessage =
                    "Receiving could not be loaded. " +
                    "Reload the page or contact an administrator.";

                console.error("[BeamCustom Receiving Initialization]", error);
            }
        },

        formatNumber(v) {
            return Number.isFinite(Number(v))
                ? Number(v).toLocaleString("en-US")
                : "0";
        },
        get actualNumber() {
            return this.actual !== "" &&
                Number.isSafeInteger(Number(this.actual)) &&
                Number(this.actual) >= 0
                ? Number(this.actual)
                : null;
        },
        get quantityDifference() {
            return this.actualNumber === null
                ? null
                : this.actualNumber - this.expected;
        },
        get mismatch() {
            return this.quantityVerified && this.actualNumber !== this.expected;
        },
        get quantityMatches() {
            return this.quantityVerified && this.actualNumber === this.expected;
        },
        matchBookingQuantity() {
            if (!this.initialized) return;
            this.actual = String(this.expected);
            this.quantityVerified = true;
            this.errorMessage = "";
            this.clearAllocation();
        },
        quantityChanged() {
            this.quantityVerified = this.actualNumber !== null;
            this.clearAllocation();
        },
        get capacity() {
            if (this.planningMode === "manual") {
                const count = Number(this.manualPalletCount);
                return this.manualPalletCount !== "" &&
                    Number.isSafeInteger(count) &&
                    count > 0 &&
                    count <= this.expected
                    ? Math.ceil(this.expected / count)
                    : 0;
            }
            const cap = Number(this.perPallet);
            return this.perPallet !== "" && Number.isSafeInteger(cap) && cap > 0
                ? cap
                : 0;
        },
        get needed() {
            if (!this.initialized || !this.capacity) return 0;
            return this.planningMode === "manual"
                ? Number(this.manualPalletCount)
                : Math.ceil(this.expected / this.capacity);
        },
        get availableLines() {
            return [...new Set(this.slots.map((s) => s.line))].sort((a, b) =>
                a.localeCompare(b, undefined, { numeric: true }),
            );
        },
        get canSuggest() {
            return (
                this.initialized &&
                this.quantityMatches &&
                this.needed > 0 &&
                this.slots.length > 0
            );
        },
        locationKey(s) {
            return `${s.line}|${s.slot}`;
        },
        locationLabel(s) {
            return `Line ${s.line} / Slot ${s.slot} — ${s.occupied > 0 ? (s.summary || "Occupied") + " (shared)" : "Empty"}`;
        },
        isLocationUsed(key, index) {
            return this.allocation.some(
                (row, i) => i !== index && row.key === key,
            );
        },
        clearAllocation() {
            this.allocation = [];
            this.confirmed = false;
            this.submitting = false;
        },
        planningChanged() {
            this.clearAllocation();
        },
        capacityChanged() {
            this.clearAllocation();
        },
        staffChanged() {
            this.confirmed = false;
        },
        locationChanged() {
            this.confirmed = false;
        },
        suggest() {
            this.clearAllocation();
            if (!this.canSuggest) {
                this.errorMessage =
                    this.slots.length === 0
                        ? "No warehouse locations exist. Add a line and slot first."
                        : "Verify the received quantity and pallet plan.";
                return;
            }
            this.errorMessage = "";
            const preferred = String(this.preferredLine);
            const ordered = [...this.slots].sort((a, b) => {
                const aP = preferred !== "" && a.line === preferred,
                    bP = preferred !== "" && b.line === preferred;
                if (aP !== bP) return aP ? -1 : 1;
                return (
                    a.line.localeCompare(b.line, undefined, {
                        numeric: true,
                    }) ||
                    a.slot.localeCompare(b.slot, undefined, { numeric: true })
                );
            });
            let remaining = this.expected;
            this.allocation = Array.from({ length: this.needed }, (_, i) => {
                const qty =
                    this.planningMode === "manual"
                        ? Math.floor(this.expected / this.needed) +
                          (i < this.expected % this.needed ? 1 : 0)
                        : Math.min(remaining, this.capacity);
                remaining -= qty;
                return { key: this.locationKey(ordered[i % ordered.length]), qty };
            });
        },
        get valid() {
            if (!this.canSuggest || this.allocation.length !== this.needed)
                return false;
            const keys = new Set(this.slots.map((s) => this.locationKey(s)));
            return this.allocation.every((row, i) => {
                const qty =
                    this.planningMode === "manual"
                        ? Math.floor(this.expected / this.needed) +
                          (i < this.expected % this.needed ? 1 : 0)
                        : Math.min(
                              this.capacity,
                              Math.max(0, this.expected - i * this.capacity),
                          );
                if (
                    !row.key ||
                    !keys.has(row.key) ||
                    Number(row.qty) !== qty
                )
                    return false;
                return true;
            });
        },
        get ready() {
            return (
                this.initialized &&
                this.quantityMatches &&
                this.valid &&
                this.warehousePic !== "" &&
                this.porter !== "" &&
                this.confirmed &&
                !this.submitting
            );
        },
        submitForm(event) {
            if (this.submitting || !this.ready) {
                event.preventDefault();
                this.errorMessage =
                    "Complete quantity verification, locations, staff and confirmation.";
                return;
            }
            this.submitting = true;
        },
    };
}
