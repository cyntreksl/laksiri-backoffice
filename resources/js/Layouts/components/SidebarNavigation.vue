<script setup>
import {Link, router, usePage} from '@inertiajs/vue3';
import logo from "../../../images/logo_main.png";
import {reactive, ref} from "vue";

const props = defineProps({
    isSidebarExpanded: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['menu-select', 'close-panel']);

const page = usePage();

const current = route().current();
const mainRoute = current.split(".")[0];
const activeMenu = ref(mainRoute);
const childMenuList = reactive([]);
const activeTitle = ref("");

const setSidebarState = () => {
    if (props.isSidebarExpanded) {
        document.body.classList.add("is-sidebar-open");
    } else {
        document.body.classList.remove("is-sidebar-open");
    }
};

const isActive = (item) => {
    const current = route().current();
    if (current !== item.route) return false;

    const routeParams = route().params || {};
    const routeQuery = route().query || {};
    const routeView = routeParams.view || routeQuery.view;
    const itemView = item.params?.view;

    if (itemView !== undefined) {
        return routeView === itemView;
    } else {
        return routeView === undefined;
    }
}

const openSideBar = () => {
    localStorage.setItem("sidebar-expanded", true);
    document.body.classList.add("is-sidebar-open");
    props.isSidebarExpanded = true;
};

const closeSideBar = () => {
    localStorage.setItem("sidebar-expanded", false);
    document.body.classList.remove("is-sidebar-open");
};

const changeSidePanelTitle = (title) => {
    activeTitle.value = title;
};

const setMenu = (menu) => {
    switch (menu) {
        case "pickups":
            let pickupMenu = [];

            if (usePage().props.user.permissions.includes("pickups.create")) {
                pickupMenu.splice(2, 0, { title: "Create Job", route: "pickups.create" });
            }

            if (usePage().props.user.permissions.includes("pickups.pending pickups")) {
                pickupMenu.splice(
                    2,
                    0,
                    {
                        title: "Pending Jobs",
                        route: "pickups.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("pickups.show pickup order")) {
                pickupMenu.splice(
                    2,
                    0,
                    {
                        title: "Pickup Ordering",
                        route: "pickups.ordering",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("pickups.show pickup exceptions")) {
                pickupMenu.splice(
                    2,
                    0,
                    {
                        title: "Pickup Exceptions",
                        route: "pickups.exceptions",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("pickups.index")) {
                pickupMenu.splice(
                    2,
                    0,
                    {
                        title: "All Pickups",
                        route: "pickups.all",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("pickups.trash_pickups")) {
                pickupMenu.splice(
                    2,
                    0,
                    {
                        title: "Trashed Jobs",
                        route: "pickups.index",
                        params: { view: 'trashed' }
                    }
                );
            }

            childMenuList.splice(0, childMenuList.length, ...pickupMenu);
            changeSidePanelTitle("Pickups");
            break;
        case "hbls":
            let hblMenu = [];

            if (usePage().props.user.permissions.includes("hbls.create")) {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "Create HBL",
                        route: "hbls.create",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("hbls.index") && usePage().props.auth.user.roles[0].name !== 'call center') {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "All HBL",
                        route: "hbls.index",
                    }
                );
            }else if (usePage().props.user.permissions.includes("hbls.index") && usePage().props.auth.user.roles[0].name === 'call center') {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "All HBL",
                        route: "call-center.hbls.index",
                    }
                );
            } else if (usePage().props.user.permissions.includes("hbls.index") && usePage().props.auth.user.roles[0].name === 'finance team')
            {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "All HBL",
                        route: "finance.hbls.index",
                    }
                );
            }
            else if (usePage().props.user.permissions.includes("hbls.index") && usePage().props.auth.user.roles[0].name === 'clearance team')
            {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "All HBL",
                        route: "finance.hbls.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("hbls.hbl finance approval list") && usePage().props.currentBranch.type === 'Destination') {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "Approve HBLs",
                        route: "finance.hbls.approve-hbl",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("hbls.finance approved hbl list") && usePage().props.currentBranch.type === 'Destination') {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "Approved HBLs",
                        route: "finance.hbls.approved-hbl",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("mhbls.index")) {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "All MHBL",
                        route: "mhbls.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("hbls.show door to door list")) {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "Door to Door HBL",
                        route: "hbls.door-to-door-list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("delivers.assign release to driver") && usePage().props.currentBranch.type === 'Destination') {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "Door to Door HBL",
                        route: "call-center.hbls.door-to-door-list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("hbls.show cancelled hbls")) {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "Cancelled HBL",
                        route: "hbls.cancelled-hbls",
                    }
                );
            }
            if (usePage().props.user.permissions.includes("hbls.show draft hbls")) {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "Draft HBL",
                        route: "hbls.draft-list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("delivers.show deliver order") && usePage().props.currentBranch.type === 'Destination') {
                hblMenu.splice(
                    2,
                    0,
                    {
                        title: "Deliver Ordering",
                        route: "delivery.ordering",
                    }
                );
            }
            childMenuList.splice(0, childMenuList.length, ...hblMenu);
            changeSidePanelTitle("HBL");
            break;
        case "back-office":
            let backOfficeMenu = [];

            if (usePage().props.user.permissions.includes("cash.index")) {
                backOfficeMenu.splice(
                    2,
                    0,
                    {
                        title: "Cash Settlements",
                        route: "back-office.cash-settlements.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("warehouse.index")) {
                backOfficeMenu.splice(
                    2,
                    0,
                    {
                        title: "Warehouse",
                        route: "back-office.warehouses.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("cash.index")) {
                backOfficeMenu.splice(
                    2,
                    0,
                    {
                        title: "Due Payments",
                        route: "back-office.duepayments.duePaymentIndex",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...backOfficeMenu
            );
            changeSidePanelTitle("Back Office");
            break;
        case "container-payment":
            let containerPaymentMenu = [];
            if (usePage().props.user.permissions.includes("payment-container.index")) {
                containerPaymentMenu.splice(
                    2,
                    0,
                    {
                        title: "Payment Requests",
                        route: "container-payment.request",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("payment-container.approved list")) {
                containerPaymentMenu.splice(
                    2,
                    0,
                    {
                        title: "Approved Payment Requests",
                        route: "approved-container-payments",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("payment-container.completed payment requests")) {
                containerPaymentMenu.splice(
                    2,
                    0,
                    {
                        title: "Completed Payment Requests",
                        route: "container-payment.showCompletedContainerPayment",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("payment-container.refund list")) {
                containerPaymentMenu.splice(
                    2,
                    0,
                    {
                        title: "Refunds",
                        route: "container-payment.showContainerPaymentRefund",
                    }
                );
            }

            childMenuList.splice(
                0,
                childMenuList.length,
                ...containerPaymentMenu
            );
            changeSidePanelTitle("Container Payments");
            break;
        case "gate-controller":
            let gateControllerMenu = [];

            if (usePage().props.user.permissions.includes("mark-shipment-arrived-to-warehouse")) {
                gateControllerMenu.splice(
                    2,
                    0,
                    {
                        title: "Inbound Shipments",
                        route: "gate-control.inbound-shipments.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("mark-shipment-depart-from-warehouse")) {
                gateControllerMenu.splice(
                    2,
                    0,
                    {
                        title: "Outbound Containers",
                        route: "gate-control.outbound-shipments.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("mark-gate-pass")) {
                gateControllerMenu.splice(
                    2,
                    0,
                    {
                        title: "Gate Pass Management",
                        route: "gate-control.outbound-gate-passes.index",
                    }
                );
            }

            childMenuList.splice(
                0,
                childMenuList.length,
                ...gateControllerMenu
            );
            changeSidePanelTitle("Gate Control");
            break;
        case "reception":
            let receptionMenu = [];

            if (usePage().props.user.permissions.includes("customer-queue.issue token")) {
                receptionMenu.splice(
                    2,
                    0,
                    {
                        title: "Issue tokens",
                        route: "call-center.reception.queue.hbl-list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show reception calling queue")) {
                receptionMenu.splice(
                    2,
                    0,
                    {
                        title: "Receptionist Queue",
                        route: "call-center.reception.queue.list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show reception verified list")) {
                receptionMenu.splice(
                    2,
                    0,
                    {
                        title: "Verified List",
                        route: "call-center.reception.show.verified",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...receptionMenu
            );
            changeSidePanelTitle("Reception Verifications");
            break;
        case "verifications":
            let verificationMenu = [];

            if (usePage().props.user.permissions.includes("customer-queue.show document verification queue")) {
                verificationMenu.splice(
                    2,
                    0,
                    {
                        title: "Document Verification Queue",
                        route: "call-center.verification.queue.list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show document verified list")) {
                verificationMenu.splice(
                    2,
                    0,
                    {
                        title: "Verified List",
                        route: "call-center.verification.show.verified",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...verificationMenu
            );
            changeSidePanelTitle("Document Verifications");
            break;
        case "cashier":
            let cashierMenu = [];

            if (usePage().props.user.permissions.includes("customer-queue.show cashier calling queue")) {
                cashierMenu.splice(
                    2,
                    0,
                    {
                        title: "Cashier Queue",
                        route: "call-center.cashier.queue.list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show cashier paid list")) {
                cashierMenu.splice(
                    2,
                    0,
                    {
                        title: "Paid List",
                        route: "call-center.cashier.show.paid",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...cashierMenu
            );
            changeSidePanelTitle("Cashier");
            break;
        case "package":
            let packageMenu = [];

            if (usePage().props.user.permissions.includes("customer-queue.show package calling screen")) {
                packageMenu.splice(
                    2,
                    0,
                    {
                        title: "Package Calling Screen",
                        route: "call-center.queue.screens.package",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show package calling queue")) {
                packageMenu.splice(
                    2,
                    0,
                    {
                        title: "Package Calling Queue",
                        route: "call-center.package.queue.list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show package released list")) {
                packageMenu.splice(
                    2,
                    0,
                    {
                        title: "Released List",
                        route: "call-center.package.show.released.list",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...packageMenu
            );
            changeSidePanelTitle("Package Queue");
            break;
        case "examination":
            let examinationMenu = [];

            if (usePage().props.user.permissions.includes("customer-queue.show document verification queue")) {
                examinationMenu.splice(
                    2,
                    0,
                    {
                        title: "Examination Queue",
                        route: "call-center.examination.queue.list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show document verified list")) {
                examinationMenu.splice(
                    2,
                    0,
                    {
                        title: "Gate Pass List",
                        route: "call-center.examination.show.gate-pass",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...examinationMenu
            );
            changeSidePanelTitle("Examination");
            break;
        case "screens":
            let screenMenu = [];

            if (usePage().props.user.permissions.includes("customer-queue.show document verification calling screen")) {
                screenMenu.splice(
                    2,
                    0,
                    {
                        title: "Document Verification Queue",
                        route: "call-center.queue.screens.document-verification",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show cashier calling screen")) {
                screenMenu.splice(
                    2,
                    0,
                    {
                        title: "Cashier Queue",
                        route: "call-center.queue.screens.cashier",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("customer-queue.show examination calling screen")) {
                screenMenu.splice(
                    2,
                    0,
                    {
                        title: "Examination Queue",
                        route: "call-center.queue.screens.examination",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...screenMenu
            );
            changeSidePanelTitle("Queue Screens");
            break;
        case "loading":
            let loadingMenu = [];
            if (usePage().props.user.permissions.includes("all.shipments.index")) {
                loadingMenu.splice(
                    2,
                    0,
                    {
                        title: "All Shipments",
                        route: "loading.all-shipments",
                    }
                );
            }
            if (usePage().props.user.permissions.includes("container.index")) {
                loadingMenu.splice(
                    2,
                    0,
                    {
                        title: "Shipments",
                        route: "loading.loading-containers.index",
                    }
                );
            }
            if (usePage().props.user.permissions.includes("shipment.index")) {
                loadingMenu.splice(
                    2,
                    0,
                    {
                        title: "Loaded Shipment",
                        route: "loading.loaded-containers.index",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...loadingMenu
            );
            changeSidePanelTitle("Loading");
            break;
        case "third-party-shipments":
            let thirdPartyShipmentMenu = [];
            if (usePage().props.user.permissions.includes("third_party_shipments.create")) {
                thirdPartyShipmentMenu.splice(
                    2,
                    0,
                    {
                        title: "Create Third Party Shipment",
                        route: "third-party-shipments.multi-options",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...thirdPartyShipmentMenu
            );
            changeSidePanelTitle("Third Party Shipments");
            break;
        case "arrival":
            let arrivalMenu = [];

            if (usePage().props.user.permissions.includes("arrivals.index") && usePage().props.currentBranch.type === 'Destination') {
                arrivalMenu.splice(
                    2,
                    0,
                    {
                        title: "Shipments Arrivals",
                        route: "arrival.shipments-arrivals.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("bonded.index") && usePage().props.currentBranch.type === 'Destination') {
                arrivalMenu.splice(
                    2,
                    0,
                    {
                        title: "Bonded Warehouse",
                        route: "arrival.bonded-warehouses.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("issues.index")) {
                arrivalMenu.splice(
                    2,
                    0,
                    {
                        title: "Unloading Issues",
                        route: "arrival.unloading-issues.index",
                    }
                );
            }

            childMenuList.splice(
                0,
                childMenuList.length,
                ...arrivalMenu
            );
            changeSidePanelTitle("Arrivals");
            break;
        case "users":
            let userMenu = [];

            if (usePage().props.user.permissions.includes("users.list")) {
                userMenu.splice(
                    2,
                    0,
                    {
                        title: "System Users",
                        route: "users.index",
                    }
                );
            }
            if (usePage().props.user.permissions.includes("users.list")) {
                userMenu.splice(
                    2,
                    0,
                    {
                        title: "Drivers",
                        route: "users.drivers.index",
                    }
                );
            }
            if (usePage().props.user.permissions.includes("users.list")) {
                userMenu.splice(
                    2,
                    0,
                    {
                        title: "Customers",
                        route: "users.customers.index",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("roles.list")) {
                userMenu.splice(
                    2,
                    0,
                    {
                        title: "Roles & Permissions",
                        route: "users.roles.index",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...userMenu
            );
            changeSidePanelTitle("Users");
            break;
        case "courier":
            let courierMenu = [];
            if (usePage().props.user.permissions.includes("third-party-agents.index") && usePage().props.currentBranch.type === 'Destination'){

                courierMenu.splice(
                    2,
                    0,
                    {
                        title: "Third Party Agents",
                        route: "couriers.agents.index",
                    }
                );
            }
            if (usePage().props.user.permissions.includes("courier.create")){

                courierMenu.splice(
                    2,
                    0,
                    {
                        title: "Create Courier ",
                        route: "couriers.create",
                    }
                );
            }
            if (usePage().props.user.permissions.includes("courier.index")){
                courierMenu.splice(
                    2,
                    0,
                    {
                        title: "All Couriers ",
                        route: "couriers.index",
                    }
                );
            }
            if (usePage().props.user.permissions.includes("courier-agents.index")){
                courierMenu.splice(
                    2,
                    0,
                    {
                        title: "Courier Agents ",
                        route: "couriers.courier-agents.index",
                    }
                );
            }
            childMenuList.splice(
                0,
                childMenuList.length,
                ...courierMenu
            );
            changeSidePanelTitle("Courier");
            break;
        case "delivery":
            childMenuList.splice(
                0,
                childMenuList.length,
                {
                    title: "Delivery Warehouse",
                    route: "delivery.delivery-warehouses.index",
                },
                {
                    title: "Dispatch Point",
                    route: "delivery.dispatch-points.index",
                },
                {
                    title: "Dispatched Loads",
                    route: "delivery.dispatched-loads.index",
                }
            );
            changeSidePanelTitle("Delivery");
            break;
        case "report":
            childMenuList.splice(0, childMenuList.length, {
                title: "Payment Summery",
                route: "report.payment-summaries.index",
            });
            changeSidePanelTitle("Report");
            break;
        case "setting":
            let settingMenu = [];

            const settingsPermissionMenuMap = [
                {
                    permission: "manage_driver_zones",
                    title: "Driver Zones",
                    route: "setting.driver-zones.index"
                },
                {
                    permission: "manage_driver_areas",
                    title: "Driver Areas",
                    route: "setting.driver-areas.index"
                },
                {
                    permission: "manage_warehouse_zones",
                    title: "Warehouse Zones",
                    route: "setting.warehouse-zones.index"
                },
                {
                    permission: "manage_pricing",
                    title: "Pricing",
                    route: "setting.prices.index"
                },
                {
                    permission: "manage_package_pricing",
                    title: "Package Pricing",
                    route: "setting.package-prices.index"
                },
                {
                    permission: "manage_exceptions",
                    title: "Exceptions",
                    route: "setting.exception-names.index"
                },
                {
                    permission: "manage_package_types",
                    title: "Package Types",
                    route: "setting.package-types.index"
                },
                {
                    permission: "manage_shippers_and_consignees",
                    title: "Shipper & Consignee",
                    route: "setting.shipper-consignees.index"
                },
                {
                    permission: "air-line.index",
                    title: "Air Lines",
                    route: "setting.air-lines.index"
                },
                {
                    permission: "tax.destination tax",
                    title: "Tax",
                    route: "setting.taxes.index"
                },
                {
                    permission: "currencies.index",
                    title: "Currencies",
                    route: "setting.currencies.index"
                },
                {
                    permission: "charges.air line do charges index",
                    title: "Air Line DO Charges",
                    route: "setting.air-lines.do-charges"
                },
                {
                    permission: "charges.special do charges index",
                    title: "Special DO Charges",
                    route: "setting.special-do-charges.index"
                },
                {
                    permission: "pickup-type.index",
                    title: "Pickup Types",
                    route: "setting.pickup-types.index"
                }
            ];

            settingsPermissionMenuMap.forEach(({ permission, title, route }) => {
                if (usePage().props.user.permissions.includes(permission)) {
                    settingMenu.push({ title, route });
                }
            });

            childMenuList.splice(0, childMenuList.length, ...settingMenu);

            changeSidePanelTitle("Settings");
            break;
        case "call-center":
            let callCenterMenu = [];

            if (usePage().props.user.permissions.includes("call-center.hbl-list")) {
                callCenterMenu.splice(
                    2,
                    0,
                    {
                        title: "HBL List",
                        route: "call-center.callcenter-list",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("call-center.all-calls")) {
                callCenterMenu.splice(
                    2,
                    0,
                    {
                        title: "All Calls",
                        route: "call-center.all-calls",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("call-center.followups")) {
                callCenterMenu.splice(
                    2,
                    0,
                    {
                        title: "Follow-ups",
                        route: "call-center.followups",
                    }
                );
            }

            if (usePage().props.user.permissions.includes("call-center.appointments")) {
                callCenterMenu.splice(
                    2,
                    0,
                    {
                        title: "Appointments",
                        route: "call-center.appointments",
                    }
                );
            }

            childMenuList.splice(0, childMenuList.length, ...callCenterMenu);
            changeSidePanelTitle("Call Center");
            break;
        case "settings":
            childMenuList.splice(0, childMenuList.length, {
                title: "Zones",
                route: "settings.zones.index",
            });
            changeSidePanelTitle("Settings");
            break;
    }
    activeMenu.value = menu;
};

setMenu(mainRoute);
setSidebarState();
</script>

<template>
  <!-- Wrapper for Sidebar and Panel -->
  <div class="sidebar print:hidden">
    <!-- Main Sidebar -->
    <div class="main-sidebar">
      <div
        class="flex h-full w-full flex-col items-center border-r border-slate-150 bg-white dark:border-navy-700 dark:bg-navy-800"
      >
        <!-- Application Logo -->
        <div class="flex pt-4">
          <a :href="route('dashboard')">
            <img
              :src="logo"
              alt="logo"
              class="size-11 transition-transform duration-500 ease-in-out hover:rotate-[360deg]"
            />
          </a>
        </div>

        <!-- Main Sections Links -->
        <div
          class="is-scrollbar-hidden flex grow flex-col space-y-4 overflow-y-auto pt-6"
        >
          <!-- Dashboard -->
          <Link
            :class="[
              activeMenu === 'dashboard' ? 'bg-primary/10 text-primary' : '',
            ]"
            :href="route('dashboard')"
            class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
            x-tooltip.placement.right="'Dashboard'"
          >
            <i class="ti ti-home text-2xl"></i>
          </Link>

          <template v-if="page.props.auth.user.roles[0].name !== 'customer'">
            <!-- Pickup -->
            <a
              v-if="page.props.user.permissions.includes('pickups.create') || page.props.user.permissions.includes('pickups.index') || page.props.user.permissions.includes('pickups.show pickup exceptions') || page.props.user.permissions.includes('pickups.show pickup order') || page.props.user.permissions.includes('pickups.pending pickups')"
              :class="[
                activeMenu === 'pickups' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Pickup'"
              @click="setMenu( 'pickups'); openSideBar();"
            >
              <i class="ti ti-truck text-2xl"></i>
            </a>

            <!-- HBL -->
            <a
              v-if="page.props.user.permissions.includes('delivers.show deliver order') || page.props.user.permissions.includes('hbls.show draft hbls') || page.props.user.permissions.includes('hbls.show cancelled hbls') || page.props.user.permissions.includes('mhbls.index') || page.props.user.permissions.includes('hbls.index') || page.props.user.permissions.includes('hbls.create')"
              :class="[
                activeMenu === 'hbls' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'HBL'"
              @click="setMenu( 'hbls'); openSideBar();"
            >
              <i class="ti ti-app-window text-2xl"></i>
            </a>

            <!-- Back Office -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('cash'))"
              :class="[
                activeMenu === 'back-office'
                  ? 'bg-primary/10 text-primary'
                  : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Back Office'"
              @click="setMenu( 'back-office'); openSideBar();"
            >
              <i class="ti ti-building text-2xl"></i>
            </a>

            <!-- Destination Branch Arrivals -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('arrival')) && page.props.currentBranch.type === 'Destination'"
              :class="[
                activeMenu === 'arrival' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Arrivals'"
              @click="setMenu( 'arrival'); openSideBar();"
            >
              <i class="ti ti-inbox text-2xl"></i>
            </a>

            <!-- Reception Verifications -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show reception'))"
              :class="[
                activeMenu === 'reception' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Reception'"
              @click="setMenu( 'reception'); openSideBar();"
            >
              <i class="ti ti-rubber-stamp text-2xl"></i>
            </a>

            <!-- Document Verifications -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show document'))"
              :class="[
                activeMenu === 'verifications' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Document Verifications'"
              @click="setMenu( 'verifications'); openSideBar();"
            >
              <i class="ti ti-certificate text-2xl"></i>
            </a>

            <!-- Cashier -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show cashier'))"
              :class="[
                activeMenu === 'cashier' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Cashier'"
              @click="setMenu( 'cashier'); openSideBar();"
            >
              <i class="ti ti-wallet text-2xl"></i>
            </a>

            <!-- Boned Area Screens -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show package'))"
              :class="[
                activeMenu === 'package' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Package Queue'"
              @click="setMenu( 'package'); openSideBar();"
            >
              <i class="ti ti-package text-2xl"></i>
            </a>

            <!-- Examination  -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show examination'))"
              :class="[
                activeMenu === 'examination' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Examination'"
              @click="setMenu( 'examination'); openSideBar();"
            >
              <i class="ti ti-checkup-list text-2xl"></i>
            </a>

            <!-- Queue Screen -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.endsWith('screen'))"
              :class="[
                activeMenu === 'screens' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Queue Screens'"
              @click="setMenu( 'screens'); openSideBar();"
            >
              <i class="ti ti-screen-share text-2xl"></i>
            </a>

            <!-- Loading -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('container')) || page.props.user.permissions.some(permission => permission.startsWith('shipment'))"
              :class="[
                activeMenu === 'loading' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Loading'"
              @click="setMenu( 'loading'); openSideBar();"
            >
              <i class="ti ti-truck-loading text-2xl"></i>
            </a>

            <!-- Third Part Shipments -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('third_party_shipments')) && page.props.currentBranch.type === 'Destination'"
              :class="[
                activeMenu === 'third-party-shipments' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Third Party Shipments'"
              @click="setMenu( 'third-party-shipments'); openSideBar();"
            >
              <i class="ti ti-tir text-2xl"></i>
            </a>

            <!-- Departure Branch Arrivals -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('arrival')) && page.props.currentBranch.type === 'Departure'"
              :class="[
                activeMenu === 'arrival' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Arrivals'"
              @click="setMenu( 'arrival'); openSideBar();"
            >
              <i class="ti ti-inbox text-2xl"></i>
            </a>

            <!-- Courier Management -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('courier')) || page.props.user.permissions.some(permission => permission.startsWith('third-party-agents')) || page.props.user.permissions.some(permission => permission.startsWith('courier-agents'))"
              :class="[
                activeMenu === 'courier' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Courier'"
              @click="setMenu( 'courier'); openSideBar();"
            >
              <i class="ti ti-truck-delivery text-2xl"></i>
            </a>

            <!-- Vessel Schedules -->
            <Link
              v-if="page.props.user.permissions.includes('vessel.schedule.index')"
              :class="[
                activeMenu === 'clearance' ? 'bg-primary/10 text-primary' : '',
              ]"
              :href="route('clearance.vessel-schedule.index')"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Vessel Schedules'"
            >
              <i class="ti ti-calendar-stats text-2xl"></i>
            </Link>

            <!-- Container Payments -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('payment-container'))"
              :class="[
                activeMenu === 'container-payment' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Container Payments'"
              @click="setMenu( 'container-payment'); openSideBar();"
            >
              <i class="ti ti-container text-2xl"></i>
            </a>

            <!-- Gate Controller  -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('mark-'))"
              :class="[
                activeMenu === 'gate-controller' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Gate Controller'"
              @click="setMenu( 'gate-controller'); openSideBar();"
            >
              <i class="ti ti-spy text-2xl"></i>
            </a>

            <!-- User Management -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('users')) || page.props.user.permissions.includes('roles.list')"
              :class="[
                activeMenu === 'users' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'User Management'"
              @click="setMenu( 'users'); openSideBar();"
            >
              <i class="ti ti-users text-2xl"></i>
            </a>

            <!-- Branches -->
            <Link
              v-if="page.props.user.permissions.some(permission => permission.startsWith('branches')) || page.props.user.permissions.includes('branches.list')"
              :class="[
                activeMenu === 'branches' ? 'bg-primary/10 text-primary' : '',
              ]"
              :href="route('branches.index')"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Branches'"
            >
              <i class="ti ti-git-branch text-2xl"></i>
            </Link>

            <!-- Call Center -->
            <a
              v-if="page.props.user.permissions.some(permission => permission.startsWith('call-center.'))"
              :class="[
                activeMenu === 'call-center' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Call Center'"
              @click="setMenu( 'call-center'); openSideBar();"
            >
              <i class="ti ti-headset text-2xl"></i>
            </a>

            <!-- Tokens -->
            <Link
              v-if="page.props.user.permissions.includes('manage_tokens')"
              :class="[
                current === 'call-center.tokens.index' ? 'bg-primary/10 text-primary' : '',
              ]"
              :href="route('call-center.tokens.index')"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Tokens'"
            >
              <i class="ti ti-tag text-2xl"></i>
            </Link>

            <!-- Whatsapp -->
            <Link
              v-if="page.props.user.permissions.includes('manage_whatsapp')"
              :class="[
                activeMenu === 'whatsapp' ? 'bg-primary/10 text-primary' : '',
              ]"
              :href="route('whatsapp.index')"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Whatsapp'"
            >
              <i class="ti ti-brand-whatsapp text-2xl"></i>
            </Link>
          </template>
        </div>

        <!-- Bottom Links -->
        <div class="flex flex-col items-center space-y-3 py-3">
          <Link
            :class="[
              activeMenu === 'driver' ? 'bg-primary/10 text-primary' : '',
            ]"
            :href="route('file-manager.index')"
            class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
            x-tooltip.placement.right="'File Manager'"
          >
            <i class="ti ti-brand-onedrive text-2xl"></i>
          </Link>
          <!-- Settings -->
          <template v-if="page.props.auth.user.roles[0].name !== 'customer'">
            <a
              v-if="! page.props.user.roles.includes('viewer')"
              :class="[
                activeMenu === 'setting' ? 'bg-primary/10 text-primary' : '',
              ]"
              class="flex size-11 items-center justify-center rounded-lg outline-none transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
              x-tooltip.placement.right="'Setting'"
              @click="setMenu( 'setting'); openSideBar();"
            >
              <svg
                class="size-7"
                fill="none"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M2 12.947v-1.771c0-1.047.85-1.913 1.899-1.913 1.81 0 2.549-1.288 1.64-2.868a1.919 1.919 0 0 1 .699-2.607l1.729-.996c.79-.474 1.81-.192 2.279.603l.11.192c.9 1.58 2.379 1.58 3.288 0l.11-.192c.47-.795 1.49-1.077 2.279-.603l1.73.996a1.92 1.92 0 0 1 .699 2.607c-.91 1.58-.17 2.868 1.639 2.868 1.04 0 1.899.856 1.899 1.912v1.772c0 1.047-.85 1.912-1.9 1.912-1.808 0-2.548 1.288-1.638 2.869.52.915.21 2.083-.7 2.606l-1.729.997c-.79.473-1.81.191-2.279-.604l-.11-.191c-.9-1.58-2.379-1.58-3.288 0l-.11.19c-.47.796-1.49 1.078-2.279.605l-1.73-.997a1.919 1.919 0 0 1-.699-2.606c.91-1.58.17-2.869-1.639-2.869A1.911 1.911 0 0 1 2 12.947Z"
                  fill="currentColor"
                  fill-opacity="0.3"
                ></path>
                <path
                  d="M11.995 15.332c1.794 0 3.248-1.464 3.248-3.27 0-1.807-1.454-3.272-3.248-3.272-1.794 0-3.248 1.465-3.248 3.271 0 1.807 1.454 3.271 3.248 3.271Z"
                  fill="currentColor"
                ></path>
              </svg>
            </a>
          </template>
        </div>
      </div>
    </div>

    <!-- Sidebar Panel -->
    <div class="sidebar-panel">
      <div
        class="flex h-full grow flex-col bg-white pl-[var(--main-sidebar-width)] dark:bg-navy-750"
      >
        <!-- Sidebar Panel Header -->
        <div class="flex h-18 w-full items-center justify-between pl-4 pr-1">
          <p
            class="text-base tracking-wider text-slate-800 dark:text-navy-100"
          >
            {{ activeTitle }}
          </p>
          <button
            class="btn size-7 rounded-full p-0 text-primary hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-accent-light/80 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 xl:hidden"
            @click="closeSideBar"
          >
            <svg
              class="size-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M15 19l-7-7 7-7"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
              ></path>
            </svg>
          </button>
        </div>

        <!-- Sidebar Panel Body -->
        <div
          class="h-[calc(100%-4.5rem)] overflow-x-hidden pb-6 simplebar-scrollable-y"
          data-simplebar="init"
        >
          <div class="simplebar-wrapper" style="margin: 0px 0px -24px">
            <div class="simplebar-height-auto-observer-wrapper">
              <div class="simplebar-height-auto-observer"></div>
            </div>
            <div class="simplebar-mask">
              <div class="simplebar-offset" style="right: 0px; bottom: 0px">
                <div
                  aria-label="scrollable content"
                  class="simplebar-content-wrapper"
                  role="region"
                  style="height: 100%; overflow: hidden scroll"
                  tabindex="0"
                >
                  <div
                    class="simplebar-content"
                    style="padding: 1px 1px 24px"
                  >
                    <ul class="flex flex-1 flex-col px-4 font-inter">
                      <li v-for="item in childMenuList" :key="item.title">
                        <Link
                          :class="isActive(item) ? 'afont-medium text-primary dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 rounded-lg hover:bg-neutral-300 dark:text-navy-200 dark:hover:text-navy-50 dark:hover:bg-neutral-500'"
                          :href="route(item.route, item.params || {})"
                          class="flex py-2 text-xs+ tracking-wide outline-none transition-colors duration-300 ease-in-out font-medium text-primary dark:text-accent-light"
                        >
                          <span class="ml-2"> {{ item.title }}</span>
                        </Link>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <div
              class="simplebar-placeholder"
              style="width: 240px; height: 686px"
            ></div>
          </div>
          <div
            class="simplebar-track simplebar-horizontal"
            style="visibility: hidden"
          >
            <div
              class="simplebar-scrollbar"
              style="width: 0px; display: none"
            ></div>
          </div>
          <div
            class="simplebar-track simplebar-vertical"
            style="visibility: visible"
          >
            <div
              class="simplebar-scrollbar"
              style="
                height: 126px;
                display: block;
                transform: translate3d(0px, 0px, 0px);
              "
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
