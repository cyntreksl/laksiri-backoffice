<script setup>
import {Link, router, usePage} from '@inertiajs/vue3';
import logo from "../../../images/logo_main.png";
import {reactive, ref, computed} from "vue";
import Menu from 'primevue/menu';
import Button from 'primevue/button';

const page = usePage();

const current = route().current();
const mainRoute = current.split(".")[0];
const activeMenu = ref(mainRoute);
const childMenuList = reactive([]);
const activeTitle = ref("");

const isSidebarExpanded = ref(
    localStorage.getItem("sidebar-expanded") === "true"
);

const setSidebarState = () => {
    if (isSidebarExpanded.value) {
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
    localStorage.setItem("sidebar-expanded", "true");
    document.body.classList.add("is-sidebar-open");
    isSidebarExpanded.value = true;
};

const closeSideBar = () => {
    localStorage.setItem("sidebar-expanded", "false");
    document.body.classList.remove("is-sidebar-open");
    isSidebarExpanded.value = false;
    childMenuList.splice(0, childMenuList.length);
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

    // Only open the sidebar if there are child menu items
    if (childMenuList.length > 0) {
        openSideBar();
    } else {
        closeSideBar();
    }
};

// PrimeVue menu model
const menuModel = ref([
    {
        label: 'Dashboard',
        icon: 'ti ti-home text-2xl',
        command: () => {
            closeSideBar();
            router.visit(route('dashboard'));
        }
    },
    {
        label: 'Pickup',
        icon: 'ti ti-truck text-2xl',
        visible: () => page.props.user.permissions.includes('pickups.create') ||
                   page.props.user.permissions.includes('pickups.index') ||
                   page.props.user.permissions.includes('pickups.show pickup exceptions') ||
                   page.props.user.permissions.includes('pickups.show pickup order') ||
                   page.props.user.permissions.includes('pickups.pending pickups') &&
            page.props.currentBranch.type === 'Departure',
        command: () => {
            setMenu('pickups');
        }
    },
    {
        label: 'HBL',
        icon: 'ti ti-app-window text-2xl',
        visible: () => page.props.user.permissions.includes('delivers.show deliver order') ||
                   page.props.user.permissions.includes('hbls.show draft hbls') ||
                   page.props.user.permissions.includes('hbls.show cancelled hbls') ||
                   page.props.user.permissions.includes('mhbls.index') ||
                   page.props.user.permissions.includes('hbls.index') ||
                   page.props.user.permissions.includes('hbls.create') &&
            page.props.currentBranch.type === 'Departure',
        command: () => {
            setMenu('hbls');
        }
    },
    {
        label: 'Back Office',
        icon: 'ti ti-building text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('cash')) &&
            page.props.currentBranch.type === 'Departure',
        command: () => {
            setMenu('back-office');
        }
    },
    {
        label: 'Arrivals',
        icon: 'ti ti-inbox text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('arrival')) &&
                   page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('arrival');
        }
    },
    {
        label: 'Reception',
        icon: 'ti ti-rubber-stamp text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show reception')) && page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('reception');
        }
    },
    {
        label: 'Document Verifications',
        icon: 'ti ti-certificate text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show document')) && page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('verifications');
        }
    },
    {
        label: 'Cashier',
        icon: 'ti ti-wallet text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show cashier')) && page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('cashier');
        }
    },
    {
        label: 'Package Queue',
        icon: 'ti ti-package text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show package')) && page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('package');
        }
    },
    {
        label: 'Examination',
        icon: 'ti ti-checkup-list text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('customer-queue.show examination')) && page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('examination');
        }
    },
    {
        label: 'Queue Screens',
        icon: 'ti ti-screen-share text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.endsWith('screen')) && page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('screens');
        }
    },
    {
        label: 'Loading',
        icon: 'ti ti-truck-loading text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('container')) ||
                   page.props.user.permissions.some(permission => permission.startsWith('shipment')) &&
            page.props.currentBranch.type === 'Departure',
        command: () => {
            setMenu('loading');
        }
    },
    {
        label: 'Third Party Shipments',
        icon: 'ti ti-tir text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('third_party_shipments')) &&
                   page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('third-party-shipments');
        }
    },
    {
        label: 'Courier',
        icon: 'ti ti-truck-delivery text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('courier')) ||
                   page.props.user.permissions.some(permission => permission.startsWith('third-party-agents')) ||
                   page.props.user.permissions.some(permission => permission.startsWith('courier-agents')) &&
            page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('courier');
        }
    },
    {
        label: 'Vessel Schedules',
        icon: 'ti ti-calendar-stats text-2xl',
        visible: () => page.props.user.permissions.includes('vessel.schedule.index')  &&
            page.props.currentBranch.type === 'Destination',
        command: () => {
            closeSideBar();
            router.visit(route('clearance.vessel-schedule.index'));
        }
    },
    {
        label: 'Container Payments',
        icon: 'ti ti-container text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('payment-container'))  &&
            page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('container-payment');
        }
    },
    {
        label: 'Gate Controller',
        icon: 'ti ti-spy text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('mark-'))  &&
            page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('gate-controller');
        }
    },
    {
        label: 'User Management',
        icon: 'ti ti-users text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('users')) ||
                   page.props.user.permissions.includes('roles.list'),
        command: () => {
            setMenu('users');
        }
    },
    {
        label: 'Branches',
        icon: 'ti ti-git-branch text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('branches')) ||
                   page.props.user.permissions.includes('branches.list'),
        command: () => {
            closeSideBar();
            router.visit(route('branches.index'));
        }
    },
    {
        label: 'Call Center',
        icon: 'ti ti-headset text-2xl',
        visible: () => page.props.user.permissions.some(permission => permission.startsWith('call-center.')) && page.props.currentBranch.type === 'Destination',
        command: () => {
            setMenu('call-center');
        }
    },
    {
        label: 'Tokens',
        icon: 'ti ti-tag text-2xl',
        visible: () => page.props.user.permissions.includes('manage_tokens') && page.props.currentBranch.type === 'Destination',
        command: () => {
            closeSideBar();
            router.visit(route('call-center.tokens.index'));
        }
    },
    {
        label: 'Whatsapp',
        icon: 'ti ti-brand-whatsapp text-2xl',
        visible: () => page.props.user.permissions.includes('manage_whatsapp'),
        command: () => {
            closeSideBar();
            router.visit(route('whatsapp.index'));
        }
    },
    {
        label: 'File Manager',
        icon: 'ti ti-brand-onedrive text-2xl',
        command: () => {
            closeSideBar();
            router.visit(route('file-manager.index'));
        }
    },
    {
        label: 'Settings',
        icon: 'ti ti-settings text-2xl',
        visible: () => !page.props.user.roles.includes('viewer') &&
                   page.props.auth.user.roles[0].name !== 'customer',
        command: () => {
            setMenu('setting');
        }
    }
]);

// Split into top and bottom menus for layout
const bottomLabels = ['Whatsapp', 'File Manager', 'Settings'];
const topMenuModel = computed(() =>
    menuModel.value.filter((item) => !bottomLabels.includes(item.label))
);
const bottomMenuModel = computed(() =>
    menuModel.value.filter((item) => bottomLabels.includes(item.label))
);

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

        <!-- Main Sections Links using PrimeVue Menu -->
        <div class="is-scrollbar-hidden flex grow flex-col pt-6">
          <Menu
            :model="topMenuModel"
            :pt="{
              root: 'w-full',
              menu: 'flex flex-col space-y-3',
              menuitem: 'w-full',
              content: ({ context }) => [
                'rounded-lg transition-all duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25',
                {
                  'bg-primary/10 text-primary dark:text-primary': context.focused
                }
              ],
              action: 'flex items-center justify-center w-full',
              icon: '!w-8',
              label: 'hidden'
            }"
            style="min-width: 0; border: 0"
            class="w-full"
          >
            <template #item="{ item, props }">
              <a
                v-if="item.visible ? item.visible() : true"
                v-tooltip.right="item.label"
                class="focus:outline-none !rounded-full"
                v-bind="props.action"
                @click="item.command"
              >
                <span :class="item.icon"></span>
              </a>
            </template>
          </Menu>
        </div>

        <!-- Bottom Links -->
        <div class="flex flex-col items-center space-y-3 py-3 w-full px-3">
          <Menu
            :model="bottomMenuModel"
            :pt="{
              root: 'w-full',
              menu: 'flex flex-col space-y-3',
              menuitem: 'w-full',
              content: ({ context }) => [
                'rounded-lg transition-all duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25',
                {
                  'bg-primary/10 text-primary dark:text-primary': context.focused
                }
              ],
              action: 'flex items-center justify-center w-full p-4',
              icon: 'text-2xl',
              label: 'hidden'
            }"
            class="w-full border-0"
            style="min-width: 0; border: 0"
          >
            <template #item="{ item, props }">
              <a
                v-if="item.visible ? item.visible() : true"
                v-tooltip.right="item.label"
                class="focus:outline-none rounded-full"
                v-bind="props.action"
                @click="item.command"
              >
                <span :class="item.icon"></span>
              </a>
            </template>
          </Menu>
        </div>
      </div>
    </div>

    <!-- Sidebar Panel -->
    <div class="sidebar-panel">
      <div
        class="flex h-full grow flex-col bg-white pl-[var(--main-sidebar-width)] dark:bg-navy-750"
      >
        <!-- Sidebar Panel Header -->
        <div class="flex h-16 w-full items-center justify-between pl-4 pr-1">
          <p
            class="text-lg font-semibold tracking-wide text-slate-800 dark:text-navy-100"
          >
            {{ activeTitle }}
          </p>
          <Button
            aria-label="Close sidebar"
            class="p-button-text p-button-sm p-button-rounded"
            icon="pi pi-times"
            @click="closeSideBar"
          />
        </div>

        <!-- Sidebar Panel Body using PrimeVue Panel -->
        <div class="h-[calc(100%-4.5rem)] overflow-x-hidden pb-6 simplebar-scrollable-y">
          <div class="p-4">
            <div class="flex flex-col space-y-1">
              <Link
                v-for="item in childMenuList"
                :key="item.title"
                :class="[
                  'p-3 rounded-lg transition-all duration-200 flex items-center',
                  'focus:outline-none focus:ring-2 focus:ring-primary/30',
                  isActive(item)
                    ? 'bg-primary/10 text-primary font-medium shadow-sm'
                    : 'text-slate-700 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-600'
                ]"
                :href="route(item.route, item.params || {})"
              >
                <span class="ml-2 text-sm">{{ item.title }}</span>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Additional custom styles for better UI/UX */
.sidebar {
  --main-sidebar-width: 5rem;
}

.main-sidebar {
  width: var(--main-sidebar-width);
  transition: all 0.3s ease;
}

.sidebar-panel {
  transition: all 0.3s ease;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
}

/* Focus styles for accessibility */
a:focus-visible,
button:focus-visible {
  outline: 2px solid #3B82F6;
  outline-offset: 2px;
}

/* Hover effects for better interactivity */
a:hover {
  transform: translateX(2px);
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .main-sidebar {
    position: fixed;
    z-index: 1000;
  }

  .sidebar-panel {
    position: fixed;
    z-index: 999;
  }
}

/* Spacing and icon sizes for the main menu are applied via PrimeVue pt classes on the Menu components */
</style>
